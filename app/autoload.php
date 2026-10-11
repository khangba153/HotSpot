<?php
declare(strict_types=1);

function loadHotSpotClass(string $className): void
{
    $namespacePrefix = 'HotSpot\\';
    $namespacePrefixLength = strlen($namespacePrefix);
    $classNamespacePrefix = substr($className, 0, $namespacePrefixLength);

    if ($classNamespacePrefix !== $namespacePrefix) {
        return;
    }

    $relativeClassName = substr($className, $namespacePrefixLength);
    $classPathParts = explode('\\', $relativeClassName);
    $classPathParts[0] = strtolower($classPathParts[0]);

    $relativeFilePath = implode('/', $classPathParts);
    $classFilePath = __DIR__ . '/' . $relativeFilePath . '.php';

    if (is_file($classFilePath)) {
        require_once $classFilePath;
    }
}

spl_autoload_register('loadHotSpotClass');