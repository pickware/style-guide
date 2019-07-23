<?php

declare(strict_types=1);

// Copyright (c) Pickware GmbH. All rights reserved.
// This file is part of software that is released under a proprietary license.
// You must not copy, modify, distribute, make publicly available, or execute
// its contents or parts thereof without express permission by the copyright
// holder, unless otherwise permitted by law.

/*
 * This document has been generated with
 * https://mlocati.github.io/php-cs-fixer-configurator/#version:2.15.1|configurator
 * you can change this configuration by importing this file.
 */
return PhpCsFixer\Config::create()
    ->setRules(
        [
            // Add, replace or remove header comment.
            'header_comment' => [
                'comment_type' => 'comment',
                'header' => "Copyright (c) Pickware GmbH. All rights reserved.\nThis file is part of software that is released under a proprietary license.\nYou must not copy, modify, distribute, make publicly available, or execute\nits contents or parts thereof without express permission by the copyright\nholder, unless otherwise permitted by law.",
                'location' => 'after_open',
            ],
        ]
    )
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->exclude('vendor')
            ->in(__DIR__)
    );
