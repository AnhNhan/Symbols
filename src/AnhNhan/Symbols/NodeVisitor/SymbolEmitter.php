<?php
namespace AnhNhan\Symbols\NodeVisitor;

use AnhNhan\Symbols\SymbolTree;

use PHPParser_Node_Stmt_Class as PP_Class;
use PHPParser_Node_Stmt_Interface as PP_Interface;
use PHPParser_Node_Stmt_Function as PP_Function;

use PHPParser_NodeVisitorAbstract as NodeVisitor;
use PHPParser_Node as Node;

/**
 * @author Anh Nhan Nguyen <anhnhan@outlook.com>
 */
class SymbolEmitter extends NodeVisitor
{
    /**
     * @var SymbolTree
     */
    private $tree;
    private $currentFile;

    public function __construct(SymbolTree $tree)
    {
        $this->tree = $tree;
    }

    public function setCurrentFile($file)
    {
        $this->currentFile = $file;
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof PP_Class) {
            $impls = array();
            if ($node->implements) {
                foreach ($node->implements as $interfaceName) {
                    $impls[] = (string)$interfaceName;
                }
            }

            $this->tree->addClass(
                $node->namespacedName->toString(),
                $this->currentFile,
                $node->extends ? $node->extends->toString() : null,
                $node->implements ? $impls : array(),
                $node->isAbstract()
            );
        } else if ($node instanceof PP_Interface) {
            $this->tree->addInterface(
                $node->namespacedName->toString(),
                $this->currentFile,
                $node->extends ? $node->extends->toString() : null
            );
        } else if ($node instanceof PP_Function) {
            $this->tree->addFunction(
                $node->namespacedName->toString(),
                $this->currentFile
            );
        }
    }
}
