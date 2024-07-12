<?php

use DI\ContainerBuilder;
use Dotenv\Dotenv;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions([
      PDO::class => function () {
          $dbPath = $_ENV['DB_PATH'] ?? __DIR__ . '/../storage/database.sqlite';
          return new PDO('sqlite:' . $dbPath);
      },
      Environment::class => function () {
          $loader = new FilesystemLoader(__DIR__ . '/../views');
          return new Environment($loader, ['cache' => false]);
      },
      Logger::class => function () {
          $logger = new Logger('app');
          $logger->pushHandler(new StreamHandler(__DIR__ . '/../storage/logs/app.log', Logger::DEBUG));
          return $logger;
      }
]);
return $containerBuilder->build();

