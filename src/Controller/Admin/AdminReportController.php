<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Annotation\AuthValidation;
use App\Entity\UserBanHistory;
use App\Enums\BanPeriodRage;
use App\Enums\ReportType;
use App\Enums\UserBanAmount;
use App\Enums\UserBanType;
use App\Enums\UserRolesNames;
use App\Exception\DataNotFoundException;
use App\Exception\InvalidJsonDataException;
use App\Model\Admin\AdminReportAudiobookCommentsModel;
use App\Model\Admin\AdminReportListSuccessModel;
use App\Model\Admin\AdminReportModel;
use App\Model\Admin\AdminUserBanModel;
use App\Model\Admin\AdminUserModel;
use App\Model\Common\AudiobookCommentModel;
use App\Model\Error\DataNotFoundModel;
use App\Model\Error\JsonDataInvalidModel;
use App\Model\Error\NotAuthorizeModel;
use App\Model\Error\PermissionNotGrantedModel;
use App\Model\Serialization\AdminReportsSearchModel;
use App\Query\Admin\AdminReportAcceptQuery;
use App\Query\Admin\AdminReportListQuery;
use App\Query\Admin\AdminReportRejectQuery;
use App\Repository\AudiobookUserCommentRepository;
use App\Repository\ReportRepository;
use App\Repository\UserBanHistoryRepository;
use App\Repository\UserDeleteRepository;
use App\Repository\UserRepository;
use App\Service\Admin\Report\AdminReportAcceptServiceInterface;
use App\Service\Admin\Report\AdminReportRejectServiceInterface;
use App\Service\RequestServiceInterface;
use App\Service\TranslateServiceInterface;
use App\Tool\ResponseTool;
use DateTime;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

#[OA\Response(
    response   : 400,
    description: 'JSON Data Invalid',
    content    : new Model(type: JsonDataInvalidModel::class)
)]
#[OA\Response(
    response   : 404,
    description: 'Data not found',
    content    : new Model(type: DataNotFoundModel::class)
)]
#[OA\Response(
    response   : 401,
    description: 'User not authorized',
    content    : new Model(type: NotAuthorizeModel::class)
)]
#[OA\Response(
    response   : 403,
    description: 'User have no permission',
    content    : new Model(type: PermissionNotGrantedModel::class)
)]
#[OA\Tag(name: 'AdminReport')]
#[Route('/api/admin')]
class AdminReportController extends AbstractController
{
    #[Route('/report/accept', name: 'apiAdminReportAccept', methods: ['PATCH'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::ADMINISTRATOR])]
    #[OA\Patch(
        description: 'Endpoint is used to accept report',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: AdminReportAcceptQuery::class),
                type: 'object',
            ),
        ),
        responses  : [
            new OA\Response(
                response   : 200,
                description: 'Success',
            ),
        ]
    )]
    public function apiAdminReportAccept(
        Request $request,
        RequestServiceInterface $requestService,
        LoggerInterface $endpointLogger,
        TranslateServiceInterface $translateService,
        ReportRepository $reportRepository,
        MailerInterface $mailer,
        AudiobookUserCommentRepository $commentRepository,
        UserRepository $userRepository,
        UserBanHistoryRepository $banHistoryRepository,
        AdminReportAcceptServiceInterface $adminReportService,
    ): Response {
        $adminReportAcceptQuery = $requestService->getRequestBodyContent($request, AdminReportAcceptQuery::class);

        if ($adminReportAcceptQuery instanceof AdminReportAcceptQuery) {
            $report = $reportRepository->find($adminReportAcceptQuery->getReportId());

            if ($report === null || $report->getAccepted() || $report->getDenied()) {
                $endpointLogger->error('Cant find report');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('AdminReportAcceptedOrRejected')]);
            }

            if ($report->getActionId() !== null && $report->getType() === ReportType::COMMENT) {
                $comment = $commentRepository->find($report->getActionId());

                if ($comment !== null && $adminReportAcceptQuery->getBanPeriod() !== null) {
                    $user = $comment->getUser();

                    $comment->setDeleted(true);
                    $commentRepository->add($comment);

                    if ($adminReportAcceptQuery->getBanPeriod() === BanPeriodRage::SYSTEM) {
                        $bannedAmount = count($banHistoryRepository->findBy([
                            'user' => $user->getId(),
                        ]));

                        if ($bannedAmount === UserBanAmount::NONE->value) {
                            $periodTo = BanPeriodRage::HALF_DAY_BAN->value;
                        } elseif ($bannedAmount > 0 && $bannedAmount <= UserBanAmount::LOW->value) {
                            $periodTo = BanPeriodRage::ONE_DAY_BAN->value;
                        } elseif ($bannedAmount > UserBanAmount::LOW->value && $bannedAmount <= UserBanAmount::MEDIUM->value) {
                            $periodTo = BanPeriodRage::FIVE_DAY_BAN->value;
                        } elseif ($bannedAmount > UserBanAmount::MEDIUM->value && $bannedAmount <= UserBanAmount::HIGH->value) {
                            $periodTo = BanPeriodRage::ONE_MONTH_BAN->value;
                        } else {
                            $periodTo = BanPeriodRage::ONE_YEAR_BAN->value;
                        }
                    } else {
                        $periodTo = $adminReportAcceptQuery->getBanPeriod()->value;
                    }
                    $banPeriod = (new DateTime())->modify($periodTo);

                    if ($periodTo !== BanPeriodRage::NOT_BANNED->value && !$user->getUserSettings()->isAdmin() && (!$user->isBanned() || ($user->getBannedTo() === null || $user->getBannedTo() < $banPeriod))) {
                        $user
                            ->setBanned(true)
                            ->setBannedTo($banPeriod);

                        $userRepository->add($user);

                        $banHistory = new UserBanHistory($user, new DateTime(), $banPeriod, UserBanType::COMMENT);

                        $banHistoryRepository->add($banHistory);
                        $report->setBanned($banHistory);
                        $reportRepository->add($report);
                    }

                    if ($_ENV['APP_ENV'] !== 'test' && $report->getType() !== ReportType::RECRUITMENT_REQUEST && $user->getUserInformation()->getEmail()) {
                        $email = (new TemplatedEmail())
                            ->from($_ENV['INSTITUTION_EMAIL'])
                            ->to($report->getEmail() ?? $user->getUserInformation()->getEmail())
                            ->subject($translateService->getTranslation('AdminReportAcceptedOrRejected'))
                            ->htmlTemplate('emails/userBanned.html.twig')
                            ->context([
                                'name'   => $user->getUserInformation()->getFirstname(),
                                'comment' => $comment->getComment(),
                                'answer'    => $adminReportAcceptQuery->getAnswer(),
                                'dateTo' => $banPeriod->format('d.m.Y'),
                                'lang' => $request->getPreferredLanguage() !== null ? $request->getPreferredLanguage() : $translateService->getLocate(),
                            ]);
                        $mailer->send($email);
                    }
                }
            }

            $adminReportService
                ->setAdminReportAcceptQuery($adminReportAcceptQuery)
                ->setRequest($request);

            if (!$adminReportAcceptQuery->isAcceptOthers()) {
                $adminReportService->sendReportResponse($report);
            } else {
                $adminReportService->sendReportResponseToAll($report);
            }

            return ResponseTool::getResponse();
        }

        $endpointLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    #[Route('/report/reject', name: 'apiAdminReportReject', methods: ['PATCH'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::ADMINISTRATOR])]
    #[OA\Patch(
        description: 'Endpoint is used to reject report',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: AdminReportRejectQuery::class),
                type: 'object',
            ),
        ),
        responses  : [
            new OA\Response(
                response   : 200,
                description: 'Success',
            ),
        ]
    )]
    public function apiAdminReportReject(
        Request $request,
        RequestServiceInterface $requestService,
        LoggerInterface $endpointLogger,
        TranslateServiceInterface $translateService,
        ReportRepository $reportRepository,
        AdminReportRejectServiceInterface $adminReportService,
    ): Response {
        $adminReportRejectQuery = $requestService->getRequestBodyContent($request, AdminReportRejectQuery::class);

        if ($adminReportRejectQuery instanceof AdminReportRejectQuery) {
            $report = $reportRepository->find($adminReportRejectQuery->getReportId());

            if ($report === null || $report->getAccepted() || $report->getDenied()) {
                $endpointLogger->error('Cant find report');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('UserToManyReports')]);
            }

            $adminReportService
                ->setAdminReportRejectQuery($adminReportRejectQuery)
                ->setRequest($request);

            if (!$adminReportRejectQuery->isRejectOthers()) {
                $adminReportService->sendReportResponse($report);
            } else {
                $adminReportService->sendReportResponseToAll($report);
            }

            return ResponseTool::getResponse();
        }

        $endpointLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    #[Route('/report/list', name: 'apiAdminReportList', methods: ['POST'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::ADMINISTRATOR, UserRolesNames::RECRUITER])]
    #[OA\Post(
        description: 'Endpoint is used to get report list',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: AdminReportListQuery::class),
                type: 'object',
            ),
        ),
        responses  : [
            new OA\Response(
                response   : 200,
                description: 'Success',
                content    : new Model(type: AdminReportListSuccessModel::class),
            ),
        ]
    )]
    public function apiAdminReportList(
        Request $request,
        RequestServiceInterface $requestService,
        LoggerInterface $endpointLogger,
        TranslateServiceInterface $translateService,
        ReportRepository $reportRepository,
        UserDeleteRepository $userDeleteRepository,
        AudiobookUserCommentRepository $commentRepository,
        SerializerInterface $serializer,
    ): Response {
        $adminReportListQuery = $requestService->getRequestBodyContent($request, AdminReportListQuery::class);

        if ($adminReportListQuery instanceof AdminReportListQuery) {
            $reportSearchData = $adminReportListQuery->getSearchData();

            $reportSearchModel = new AdminReportsSearchModel();
            $serializer->deserialize(
                json_encode($reportSearchData),
                AdminReportsSearchModel::class,
                'json',
                [
                    AbstractNormalizer::OBJECT_TO_POPULATE             => $reportSearchModel,
                    AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true,
                ],
            );

            $successModel = new AdminReportListSuccessModel();

            $reports = $reportRepository->getReportsByPage($reportSearchModel);

            $minResult = $adminReportListQuery->getPage() * $adminReportListQuery->getLimit();
            $maxResult = $adminReportListQuery->getLimit() + $minResult;

            foreach ($reports as $index => $report) {
                if ($index < $minResult) {
                    continue;
                }

                if ($index < $maxResult) {
                    $reportModel = new AdminReportModel(
                        (string)$report->getId(),
                        $report->getType(),
                        $report->getDateAdd(),
                        $report->getAccepted(),
                        $report->getDenied(),
                    );

                    if ($report->getDescription()) {
                        $reportModel->setDescription($report->getDescription());
                    }
                    if ($report->getActionId()) {
                        $reportModel->setActionId((string)$report->getActionId());
                    }
                    if ($report->getEmail()) {
                        $reportModel->setEmail($report->getEmail());
                    }
                    if ($report->getIp()) {
                        $reportModel->setIp($report->getIp());
                    }
                    if ($report->getAnswer()) {
                        $reportModel->setAnswer($report->getAnswer());
                    }
                    if ($report->getSettleDate()) {
                        $reportModel->setSettleDate($report->getSettleDate());
                    }

                    if ($report->getUser()) {
                        $userDeleted = $userDeleteRepository->userInToDeleteList($report->getUser());

                        $reportModel->setUser(
                            new AdminUserModel(
                                (string)$report->getUser()->getId(),
                                $report->getUser()->isActive(),
                                $report->getUser()->isBanned(),
                                $report->getUser()->getUserInformation()->getEmail(),
                                $report->getUser()->getUserInformation()->getFirstname(),
                                $report->getUser()->getUserInformation()->getLastname(),
                                $report->getUser()->getDateCreate(),
                                $userDeleted,
                            ),
                        );
                    }

                    if ($report->getType() === ReportType::COMMENT && $report->getActionId() !== null) {
                        $comment = $commentRepository->find($report->getActionId());

                        if ($comment !== null) {
                            if ($comment->getParent() !== null) {
                                $children = $commentRepository->findBy(['parent' => $comment->getParent()]);
                                $parent = $comment->getParent();

                                $userModel = new AudiobookCommentModel(
                                    $parent->getUser()->getUserInformation()->getEmail(),
                                    $parent->getUser()->getUserInformation()->getFirstname(),
                                );

                                $commentModel = new AdminReportAudiobookCommentsModel(
                                    $userModel,
                                    $parent->getComment(),
                                );

                                foreach ($children as $commentChildren) {
                                    $childrenUserModel = new AudiobookCommentModel(
                                        $commentChildren->getUser()->getUserInformation()->getEmail(),
                                        $commentChildren->getUser()->getUserInformation()->getFirstname(),
                                    );
                                    $commentChildren = new AdminReportAudiobookCommentsModel(
                                        $childrenUserModel,
                                        $commentChildren->getComment(),
                                        $commentChildren->getId()->toBinary() === $comment->getId()->toBinary()
                                    );

                                    $commentModel->addChildren($commentChildren);
                                }
                            } else {
                                $userModel = new AudiobookCommentModel(
                                    $comment->getUser()->getUserInformation()->getEmail(),
                                    $comment->getUser()->getUserInformation()->getFirstname(),
                                );

                                $commentModel = new AdminReportAudiobookCommentsModel(
                                    $userModel,
                                    $comment->getComment(),
                                    true
                                );
                            }
                            $reportModel->setComment($commentModel);
                        }
                    }

                    if ($report->getActionId()) {
                        $similarReports = $reportRepository->getSimilarReportsCount($report->getActionId());
                        $reportModel->setSimilarReports(empty($similarReports) ? 0 : $similarReports[array_key_first($similarReports)]);
                    }

                    if ($report->getBanned()) {
                        $reportModel->setUserBan(
                            new AdminUserBanModel(
                                $report->getBanned()->getDateFrom(),
                                $report->getBanned()->getDateTo(),
                                $report->getBanned()->getType(),
                            ),
                        );
                    }

                    $successModel->addReport($reportModel);
                } else {
                    break;
                }
            }

            $successModel->setPage($adminReportListQuery->getPage());
            $successModel->setLimit($adminReportListQuery->getLimit());
            $successModel->setMaxPage((int)ceil(count($reports) / $adminReportListQuery->getLimit()));

            return ResponseTool::getResponse($successModel);
        }

        $endpointLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }
}
