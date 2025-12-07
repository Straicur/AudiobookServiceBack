<?php

declare(strict_types = 1);

use DAMA\DoctrineTestBundle\DAMADoctrineTestBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle;
use Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle;
use Nelmio\ApiDocBundle\NelmioApiDocBundle;
use Nelmio\CorsBundle\NelmioCorsBundle;
use Symfony\Bundle\DebugBundle\DebugBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MakerBundle\MakerBundle;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Bundle\WebProfilerBundle\WebProfilerBundle;
use Twig\Extra\TwigExtraBundle\TwigExtraBundle;

return [
    FrameworkBundle::class          => ['all' => true],
    DoctrineBundle::class           => ['all' => true],
    DoctrineMigrationsBundle::class => ['all' => true],
    TwigBundle::class               => ['all' => true],
    WebProfilerBundle::class        => ['dev' => true, 'test' => true],
    MonologBundle::class            => ['all' => true],
    DebugBundle::class              => ['dev' => true],
    MakerBundle::class              => ['dev' => true],
    NelmioApiDocBundle::class       => ['all' => true],
    TwigExtraBundle::class          => ['all' => true],
    DAMADoctrineTestBundle::class   => ['test' => true],
    NelmioCorsBundle::class         => ['all' => true],
    SecurityBundle::class           => ['all' => true],
    DoctrineFixturesBundle::class   => ['dev' => true, 'test' => true],
];
