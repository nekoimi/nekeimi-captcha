<?php
$header = <<<'EOF'
----------------------------------------------------------------------
             nekoimi <i@sakuraio.com>
                                         ------
  Copyright (c) 2017-%s https://nekoimi.com All rights reserved.
----------------------------------------------------------------------
EOF;
$header = sprintf($header, date('Y'));
return PhpCsFixer\Config::create()
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR2' => true,
        'header_comment' => [
            'commentType' => 'PHPDoc',
            'header' => $header,
            'separate' => 'none'
        ],
        'array_syntax' => [
            'syntax' => 'short'
        ],
        'single_quote' => true,
        'class_attributes_separation' => true,
        'self_accessor'  => true,
        'no_empty_statement' => true,
        'no_unused_imports' => true,
        'standardize_not_equals' => true,
        'no_leading_namespace_whitespace' => true,
        'no_extra_consecutive_blank_lines' => true,
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->exclude('vendor')
            ->in(__DIR__)
    )
    ->setUsingCache(false);