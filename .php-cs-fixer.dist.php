<?php

declare(strict_types=1);

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('lib/pear-Auth');

return (new PhpCsFixer\Config())
    ->setFinder($finder);
