<?php

declare (strict_types=1);
namespace Rector\Core\PhpParser\Node;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Case_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\Core\Exception\StopSearchException;
use Rector\Core\NodeAnalyzer\ClassAnalyzer;
use Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Rector\Core\Provider\CurrentFileProvider;
use Rector\Core\ValueObject\Application\File;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
use RectorPrefix202307\Webmozart\Assert\Assert;
/**
 * @see \Rector\Core\Tests\PhpParser\Node\BetterNodeFinder\BetterNodeFinderTest
 */
final class BetterNodeFinder
{
    /**
     * @readonly
     * @var \PhpParser\NodeFinder
     */
    private $nodeFinder;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ClassAnalyzer
     */
    private $classAnalyzer;
    /**
     * @readonly
     * @var \Rector\Core\Provider\CurrentFileProvider
     */
    private $currentFileProvider;
    /**
     * @readonly
     * @var \Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    public function __construct(NodeFinder $nodeFinder, NodeNameResolver $nodeNameResolver, ClassAnalyzer $classAnalyzer, CurrentFileProvider $currentFileProvider, SimpleCallableNodeTraverser $simpleCallableNodeTraverser)
    {
        $this->nodeFinder = $nodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->classAnalyzer = $classAnalyzer;
        $this->currentFileProvider = $currentFileProvider;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
    }
    /**
     * @template T of Node
     * @param array<class-string<T>> $types
     * @param \PhpParser\Node|mixed[] $nodes
     * @return T[]
     */
    public function findInstancesOf($nodes, array $types) : array
    {
        $foundInstances = [];
        foreach ($types as $type) {
            $currentFoundInstances = $this->findInstanceOf($nodes, $type);
            $foundInstances = \array_merge($foundInstances, $currentFoundInstances);
        }
        return $foundInstances;
    }
    /**
     * @template T of Node
     * @param class-string<T> $type
     * @param \PhpParser\Node|mixed[] $nodes
     * @return T[]
     */
    public function findInstanceOf($nodes, string $type) : array
    {
        return $this->nodeFinder->findInstanceOf($nodes, $type);
    }
    /**
     * @template T of Node
     * @param class-string<T> $type
     * @return T|null
     *
     * @param \PhpParser\Node|mixed[] $nodes
     */
    public function findFirstInstanceOf($nodes, string $type) : ?Node
    {
        Assert::isAOf($type, Node::class);
        return $this->nodeFinder->findFirstInstanceOf($nodes, $type);
    }
    /**
     * @param class-string<Node> $type
     * @param Node[] $nodes
     */
    public function hasInstanceOfName(array $nodes, string $type, string $name) : bool
    {
        Assert::isAOf($type, Node::class);
        return (bool) $this->findInstanceOfName($nodes, $type, $name);
    }
    /**
     * @param Node[] $nodes
     */
    public function hasVariableOfName(array $nodes, string $name) : bool
    {
        return $this->findVariableOfName($nodes, $name) instanceof Node;
    }
    /**
     * @api
     * @param \PhpParser\Node|mixed[] $nodes
     * @return Variable|null
     */
    public function findVariableOfName($nodes, string $name) : ?Node
    {
        return $this->findInstanceOfName($nodes, Variable::class, $name);
    }
    /**
     * @param \PhpParser\Node|mixed[] $nodes
     * @param array<class-string<Node>> $types
     */
    public function hasInstancesOf($nodes, array $types) : bool
    {
        Assert::allIsAOf($types, Node::class);
        foreach ($types as $type) {
            $foundNode = $this->nodeFinder->findFirstInstanceOf($nodes, $type);
            if (!$foundNode instanceof Node) {
                continue;
            }
            return \true;
        }
        return \false;
    }
    /**
     * @param \PhpParser\Node|mixed[] $nodes
     * @param callable(Node $node): bool $filter
     * @return Node[]
     */
    public function find($nodes, callable $filter) : array
    {
        return $this->nodeFinder->find($nodes, $filter);
    }
    /**
     * @api symfony
     * @param Node[] $nodes
     * @return ClassLike|null
     */
    public function findFirstNonAnonymousClass(array $nodes) : ?Node
    {
        // skip anonymous classes
        return $this->findFirst($nodes, function (Node $node) : bool {
            return $node instanceof Class_ && !$this->classAnalyzer->isAnonymousClass($node);
        });
    }
    /**
     * @param \PhpParser\Node|mixed[] $nodes
     * @param callable(Node $filter): bool $filter
     */
    public function findFirst($nodes, callable $filter) : ?Node
    {
        return $this->nodeFinder->findFirst($nodes, $filter);
    }
    /**
     * @param callable(Node $node): bool $filter
     */
    public function findFirstNext(Node $node, callable $filter) : ?Node
    {
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        $newStmts = $this->resolveNewStmts($parentNode);
        try {
            $foundNode = $this->findFirstInlinedNext($node, $filter, $newStmts, $parentNode);
        } catch (StopSearchException $exception) {
            return null;
        }
        // we found what we need
        if ($foundNode instanceof Node) {
            return $foundNode;
        }
        if ($parentNode instanceof Return_ || $parentNode instanceof FunctionLike) {
            return null;
        }
        if ($parentNode instanceof Node) {
            if ($parentNode instanceof FileWithoutNamespace || $parentNode instanceof Namespace_) {
                return null;
            }
            return $this->findFirstNext($parentNode, $filter);
        }
        return null;
    }
    /**
     * @template T of Node
     * @param array<class-string<T>>|class-string<T> $types
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure $functionLike
     */
    public function hasInstancesOfInFunctionLikeScoped($functionLike, $types) : bool
    {
        if (\is_string($types)) {
            $types = [$types];
        }
        $isFoundNode = \false;
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable((array) $functionLike->stmts, static function (Node $subNode) use($types, &$isFoundNode) : ?int {
            if ($subNode instanceof Class_ || $subNode instanceof Function_ || $subNode instanceof Closure) {
                return NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            foreach ($types as $type) {
                if ($subNode instanceof $type) {
                    $isFoundNode = \true;
                    return NodeTraverser::STOP_TRAVERSAL;
                }
            }
            return null;
        });
        return $isFoundNode;
    }
    /**
     * @template T of Node
     * @param array<class-string<T>>|class-string<T> $types
     * @return T[]
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure $functionLike
     */
    public function findInstancesOfInFunctionLikeScoped($functionLike, $types) : array
    {
        if (\is_string($types)) {
            $types = [$types];
        }
        /** @var T[] $foundNodes */
        $foundNodes = [];
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable((array) $functionLike->stmts, static function (Node $subNode) use($types, &$foundNodes) : ?int {
            if ($subNode instanceof Class_ || $subNode instanceof Function_ || $subNode instanceof Closure) {
                return NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            foreach ($types as $type) {
                if ($subNode instanceof $type) {
                    $foundNodes[] = $subNode;
                    return null;
                }
            }
            return null;
        });
        return $foundNodes;
    }
    /**
     * @param callable(Node $node): bool $filter
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure $functionLike
     */
    public function findFirstInFunctionLikeScoped($functionLike, callable $filter) : ?Node
    {
        if ($functionLike->stmts === null) {
            return null;
        }
        $foundNode = $this->findFirst($functionLike->stmts, $filter);
        if (!$foundNode instanceof Node) {
            return null;
        }
        if (!$this->hasInstancesOf($functionLike->stmts, [Class_::class, Function_::class, Closure::class])) {
            return $foundNode;
        }
        $scopedNode = null;
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($functionLike->stmts, static function (Node $subNode) use(&$scopedNode, $foundNode) : ?int {
            if ($subNode instanceof Class_ || $subNode instanceof Function_ || $subNode instanceof Closure) {
                if ($foundNode instanceof $subNode && $subNode === $foundNode) {
                    $scopedNode = $subNode;
                    return NodeTraverser::STOP_TRAVERSAL;
                }
                return NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if ($foundNode instanceof $subNode && $subNode === $foundNode) {
                $scopedNode = $subNode;
                return NodeTraverser::STOP_TRAVERSAL;
            }
            return null;
        });
        return $scopedNode;
    }
    /**
     * @api
     *
     * Resolve next node from any Node, eg: Expr, Identifier, Name, etc
     */
    public function resolveNextNode(Node $node) : ?Node
    {
        $currentStmt = $this->resolveCurrentStatement($node);
        if (!$currentStmt instanceof Stmt) {
            return null;
        }
        $endTokenPos = $node->getEndTokenPos();
        $nextNode = $endTokenPos < 0 || $currentStmt->getEndTokenPos() === $endTokenPos ? null : $this->findFirst($currentStmt, static function (Node $subNode) use($endTokenPos) : bool {
            return $subNode->getStartTokenPos() > $endTokenPos;
        });
        if (!$nextNode instanceof Node) {
            $parentNode = $currentStmt->getAttribute(AttributeKey::PARENT_NODE);
            if (!$this->isAllowedParentNode($parentNode)) {
                return null;
            }
            $currentStmtKey = $currentStmt->getAttribute(AttributeKey::STMT_KEY);
            /** @var StmtsAwareInterface|ClassLike|Declare_ $parentNode */
            return $parentNode->stmts[$currentStmtKey + 1] ?? null;
        }
        return $nextNode;
    }
    private function resolveCurrentStatement(Node $node) : ?Stmt
    {
        if ($node instanceof Stmt) {
            return $node;
        }
        $currentStmt = $node;
        while (($currentStmt = $currentStmt->getAttribute(AttributeKey::PARENT_NODE)) instanceof Node) {
            if ($currentStmt instanceof Stmt) {
                return $currentStmt;
            }
            /** @var Node|null $currentStmt */
            if (!$currentStmt instanceof Node) {
                return null;
            }
        }
        return null;
    }
    private function isAllowedParentNode(?Node $node) : bool
    {
        return $node instanceof StmtsAwareInterface || $node instanceof ClassLike || $node instanceof Declare_;
    }
    /**
     * Only search in next Node/Stmt
     *
     * @param Stmt[] $newStmts
     * @param callable(Node $node): bool $filter
     */
    private function findFirstInlinedNext(Node $node, callable $filter, array $newStmts, ?Node $parentNode) : ?Node
    {
        if (!$parentNode instanceof Node) {
            $nextNode = $this->resolveNextNodeFromFile($newStmts, $node);
        } elseif ($node instanceof Stmt) {
            if (!$this->isAllowedParentNode($parentNode)) {
                return null;
            }
            $currentStmtKey = $node->getAttribute(AttributeKey::STMT_KEY);
            /** @var StmtsAwareInterface|ClassLike|Declare_ $parentNode */
            $nextNode = $parentNode->stmts[$currentStmtKey + 1] ?? null;
        } else {
            $nextNode = $this->resolveNextNode($node);
        }
        if (!$nextNode instanceof Node) {
            return null;
        }
        if ($nextNode instanceof Return_ && !$nextNode->expr instanceof Expr && !$parentNode instanceof Case_) {
            throw new StopSearchException();
        }
        $found = $this->findFirst($nextNode, $filter);
        if ($found instanceof Node) {
            return $found;
        }
        return $this->findFirstInlinedNext($nextNode, $filter, $newStmts, $parentNode);
    }
    /**
     * @return Stmt[]
     */
    private function resolveNewStmts(?Node $parentNode) : array
    {
        if (!$parentNode instanceof Node) {
            // on __construct(), $file not yet a File object
            $file = $this->currentFileProvider->getFile();
            return $file instanceof File ? $file->getNewStmts() : [];
        }
        return [];
    }
    /**
     * @param Stmt[] $newStmts
     */
    private function resolveNextNodeFromFile(array $newStmts, Node $node) : ?Node
    {
        if (!$node instanceof Namespace_ && !$node instanceof FileWithoutNamespace) {
            return null;
        }
        $currentStmtKey = $node->getAttribute(AttributeKey::STMT_KEY);
        return $newStmts[$currentStmtKey + 1] ?? null;
    }
    /**
     * @template T of Node
     * @param \PhpParser\Node|mixed[] $nodes
     * @param class-string<T> $type
     */
    private function findInstanceOfName($nodes, string $type, string $name) : ?Node
    {
        Assert::isAOf($type, Node::class);
        return $this->nodeFinder->findFirst($nodes, function (Node $node) use($type, $name) : bool {
            return $node instanceof $type && $this->nodeNameResolver->isName($node, $name);
        });
    }
}
