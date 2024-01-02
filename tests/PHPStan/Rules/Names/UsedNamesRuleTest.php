<?php declare(strict_types=1);

namespace PHPStan\Rules\Names;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<UsedNamesRule>
 */
final class UsedNamesRuleTest extends RuleTestCase
{

    protected function getRule(): Rule
    {
        return new UsedNamesRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/simple-uses.php'], [
            [
                'Cannot declare class SomeNamespace\SimpleUses because the name is already in use.',
                7,
            ],
        ]);

        $this->analyse([__DIR__ . '/data/grouped-uses.php'], [
            [
                'Cannot declare interface SomeNamespace\GroupedUses because the name is already in use.',
                10,
            ],
        ]);

        $this->analyse([__DIR__ . '/data/simple-uses-under-class.php'], [
            [
                'Cannot use SomeOtherNamespace\UsesUnderClass as SimpleUsesUnderClass because the name is already in use.',
                9,
            ],
        ]);

        $this->analyse([__DIR__ . '/data/grouped-uses-under-class.php'], [
            [
                'Cannot use SomeOtherNamespace\UsesUnderClass as GroupedUsesUnderClass because the name is already in use.',
                11,
            ],
        ]);

        $this->analyse([__DIR__ . '/data/no-namespace.php'], [
            [
                'Cannot declare class NoNamespace because the name is already in use.',
                5,
            ],
            [
                'Cannot declare class NoNamespace because the name is already in use.',
                9,
            ],
        ]);

        $this->analyse([__DIR__ . '/data/multiple-namespaces.php'], [
            [
                'Cannot declare trait FirstNamespace\MultipleNamespaces because the name is already in use.',
                24,
            ],
        ]);
    }

}
