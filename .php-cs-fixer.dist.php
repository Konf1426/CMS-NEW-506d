<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in(__DIR__) // Analyser tout le projet
    ->exclude(['vendor', 'var', 'public']) // Exclure certains dossiers
    ->name('*.php') // Inclure uniquement les fichiers PHP
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return (new Config())
    ->setRules([
        '@Symfony' => true, // Utilise les règles Symfony comme base
        'array_syntax' => ['syntax' => 'short'], // Utilise les arrays courts []
        'single_quote' => true, // Utilise des quotes simples
        'no_unused_imports' => true, // Supprime les imports inutilisés
        'ordered_imports' => ['sort_algorithm' => 'alpha'], // Trie les imports
        'phpdoc_order' => true, // Trie les annotations PHPDoc
    ])
    ->setFinder($finder);
