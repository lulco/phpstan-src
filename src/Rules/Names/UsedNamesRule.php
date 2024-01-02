<?php declare(strict_types = 1);

namespace PHPStan\Rules\Names;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\Node\Stmt\Use_;
use PHPStan\Analyser\Scope;
use PHPStan\Node\FileNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use function in_array;
use function strtolower;

/**
 * @implements Rule<FileNode>
 */
final class UsedNamesRule implements Rule
{

	public function getNodeType(): string
	{
		return FileNode::class;
	}

	/**
	 * @param FileNode $node
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		$usedNames = [];
		$errors = [];
		foreach ($node->getNodes() as $oneNode) {
			if ($oneNode instanceof Namespace_) {
				$namespaceName = $oneNode->name !== null ? $oneNode->name->toString() : '';
				foreach ($oneNode->stmts as $stmt) {
					$error = $this->findErrorForNode($stmt, $namespaceName, $usedNames);
					if ($error === null) {
						continue;
					}

					$errors[] = $error;
				}
			}

			$error = $this->findErrorForNode($oneNode, '', $usedNames);
			if ($error === null) {
				continue;
			}

			$errors[] = $error;
		}

		return $errors;
	}

	/**
	 * @param array<string, string[]> $usedNames
	 */
	private function findErrorForNode(Node $node, string $namespace, array &$usedNames): ?RuleError
	{
		$lowerNamespace = strtolower($namespace);
		if ($node instanceof Use_) {
			foreach ($node->uses as $use) {
				$useAlias = $use->getAlias()->toLowerString();
				if (in_array($useAlias, $usedNames[$lowerNamespace] ?? [], true)) {
					return RuleErrorBuilder::message('Cannot use ' . $use->name->toString() . ' as ' . $use->getAlias()->toString() . ' because the name is already in use.')->line($use->getStartLine())->build();
				}
				$usedNames[$lowerNamespace][] = $useAlias;
			}
			return null;
		}

		if ($node instanceof GroupUse) {
			$useGroupPrefix = $node->prefix->toString();
			foreach ($node->uses as $use) {
				$useAlias = $use->getAlias()->toLowerString();
				if (in_array($useAlias, $usedNames[$lowerNamespace] ?? [], true)) {
					return RuleErrorBuilder::message('Cannot use ' . $useGroupPrefix . '\\' . $use->name->toString() . ' as ' . $use->getAlias()->toString() . ' because the name is already in use.')->line($use->getStartLine())->build();
				}
				$usedNames[$lowerNamespace][] = $useAlias;
			}
			return null;
		}

		if ($node instanceof ClassLike) {
			if ($node->name === null) {
				return null;
			}
			$type = 'class';
			if ($node instanceof Interface_) {
				$type = 'interface';
			} elseif ($node instanceof Trait_) {
				$type = 'trait';
			} elseif ($node instanceof Enum_) {
				$type = 'enum';
			}
			$name = $node->name->toLowerString();
			if (in_array($name, $usedNames[$lowerNamespace] ?? [], true)) {
				return RuleErrorBuilder::message('Cannot declare ' . $type . ' ' . ($namespace !== '' ? $namespace . '\\' . $node->name->toString() : $node->name->toString()) . ' because the name is already in use.')->line($node->getStartLine())->build();
			}
			$usedNames[$lowerNamespace][] = $name;
			return null;
		}

		return null;
	}

}
