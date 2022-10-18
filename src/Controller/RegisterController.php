<?php

namespace App\Controller;

use App\Model\DataNotFoundModel;
use App\Model\JsonDataInvalidModel;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * RegisterController
 */
#[OA\Response(
    response: 400,
    description: "JSON Data Invalid",
    content: new Model(type: JsonDataInvalidModel::class)
)]
#[OA\Response(
    response: 404,
    description: "Data not found",
    content: new Model(type: DataNotFoundModel::class)
)]
#[OA\Tag(name: "Register")]
class RegisterController extends AbstractController
{
    //Dodający - ma dodać plus daje mu range Guest, po czym wysyłam email z linkiem(generacja jak password że trzymam w hashu) //dodaj mu też listę od razu
    //Odbierający na getcie i on zczytuje(szuka czy jest aktywny i czy nie ma już odpowiedniej rangi),dezaktywuje i ustawia rangę User
    //Wysyłający jeszcze raz, a reszte dezaktywuje
}