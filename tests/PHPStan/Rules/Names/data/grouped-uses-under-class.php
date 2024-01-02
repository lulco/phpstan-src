<?php

namespace SomeNamespace;

final class GroupedUsesUnderClass
{
}

use SomeOtherNamespace\{
	SimpleUses,
	UsesUnderClass as GroupedUsesUnderClass,
};
