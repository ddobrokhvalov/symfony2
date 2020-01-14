<?php

use Symfony\Component\ClassLoader\UniversalClassLoader;
use Doctrine\Common\Annotations\AnnotationRegistry;
ob_start();
$loader = new UniversalClassLoader();
$loader->registerNamespaces(array(
    'Symfony'          => array(__DIR__.'/../vendor/symfony/src', __DIR__.'/../vendor/bundles'),
    'Sensio'           => __DIR__.'/../vendor/bundles',
    'JMS'              => __DIR__.'/../vendor/bundles',
    'CG'               => __DIR__.'/../vendor/cg-library/src',
    'Doctrine\\Common' => __DIR__.'/../vendor/doctrine-common/lib',
    'Doctrine\\DBAL'   => __DIR__.'/../vendor/doctrine-dbal/lib',
    'Doctrine'         => __DIR__.'/../vendor/doctrine/lib',
    'Monolog'          => __DIR__.'/../vendor/monolog/src',
    'Assetic'          => __DIR__.'/../vendor/assetic/src',
    'Metadata'         => __DIR__.'/../vendor/metadata/src',
    'Knp'              => __DIR__.'/../vendor/bundles',
    'Knp\Menu'         => __DIR__.'/../vendor/bundles/Knp/Bundle/Menu/src',
    'Sonata'           => __DIR__.'/../src',
    'OpenSky'          => __DIR__.'/../vendor/bundles',
    'FOS'              => __DIR__.'/../vendor/bundles',
));
$loader->registerPrefixes(array(
    'Twig_Extensions_' => __DIR__.'/../vendor/twig-extensions/lib',
    'Twig_'            => __DIR__.'/../vendor/twig/lib',
));

// intl
if (!function_exists('intl_get_error_code')) {
    require_once __DIR__.'/../vendor/symfony/src/Symfony/Component/Locale/Resources/stubs/functions.php';

    $loader->registerPrefixFallbacks(array(__DIR__.'/../vendor/symfony/src/Symfony/Component/Locale/Resources/stubs'));
}

$loader->registerNamespaceFallbacks(array(
    __DIR__.'/../src',
));
$loader->register();

AnnotationRegistry::registerLoader(function($class) use ($loader) {
    $loader->loadClass($class);
    return class_exists($class, false);
});
AnnotationRegistry::registerFile(__DIR__.'/../vendor/doctrine/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php');

// Swiftmailer needs a special autoloader to allow
// the lazy loading of the init file (which is expensive)
require_once __DIR__.'/../vendor/swiftmailer/lib/classes/Swift.php';
Swift::registerAutoload(__DIR__.'/../vendor/swiftmailer/lib/swift_init.php');

if (isset($_SERVER) && isset($_SERVER['HTTP_HOST']) && ($_SERVER['HTTP_HOST'] != 'k.report.armd.ru')) {
    register_shutdown_function('shutdownFunction');
}
function shutDownFunction() {
    $error = error_get_last();
    $buffer = ob_get_clean();
    if (is_array($error) && ($error['type'] == 1)) {
        ob_start();
        echo "На сайте произошла ошибка.\n\n";
        var_dump($error);
        var_dump($_SERVER);
        var_dump($_GET);
        var_dump($_POST);
        var_dump($_SERVER);
        var_dump($_SESSION);
        $body = ob_get_contents();
        ob_clean();
        mail('support-report@armd.ru', 'PHP ошибка', $body);
        echo '<div align="center"><img src="/img/Error-256.png"><br><br><h2>Извините, произошла какая-то ошибка. Наш робот отправил отчет о ней создателям.</h2></div>';
    } else {
        echo $buffer;
    }
} 

