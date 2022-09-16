<?php

declare(strict_types=1);

use Contao\EasyCodingStandard\Fixer\TypeHintOrderFixer;
use PhpCsFixer\Fixer\Comment\HeaderCommentFixer;
use PhpCsFixer\Fixer\Whitespace\MethodChainingIndentationFixer;
use SlevomatCodingStandard\Sniffs\TypeHints\DisallowArrayTypeHintSyntaxSniff;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Option;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->sets([__DIR__.'/tools/ecs/vendor/contao/easy-coding-standard/config/contao.php']);

    $ecsConfig->skip([
        MethodChainingIndentationFixer::class => [
            '*/DependencyInjection/Configuration.php',
            '*/Resources/config/*.php',
        ],
        TypeHintOrderFixer::class,
        DisallowArrayTypeHintSyntaxSniff::class => ['*Model.php'],
        '*/templates/*.html5',
    ]);

    $header = <<<EOF
Contao Portfolio Bundle for Contao Open Source CMS.
@copyright  Copyright (c) Erdmann & Freunde
@author     Erdmann & Freunde <https://erdmann-freunde.de>
@license    MIT
@link       http://github.com/erdmannfreunde/contao-portfolio-bundle
EOF;
    $ecsConfig->ruleWithConfiguration(HeaderCommentFixer::class, [
        'header' => $header,
    ]);

    $ecsConfig->parallel();
    $ecsConfig->lineEnding("\n");

    $parameters = $ecsConfig->parameters();
    $parameters->set(Option::CACHE_DIRECTORY, sys_get_temp_dir().'/ecs_default_cache');
};
