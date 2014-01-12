<?php
namespace AnhNhan\Symbols;

/**
 * @author Anh Nhan Nguyen <anhnhan@outlook.com>
 */
class SymbolTree
{
    private $classes = array();
    private $interfaces = array();
    private $functions = array();

    private $derivs = array();

    public function addClass($name, $file, $deriv, array $impls, $abstract)
    {
        $this->classes[$name] = array(
            "file"  => $file,
            "deriv" => $deriv,
            "impls" => $impls,
            "abstr" => $abstract,
        );

        if (!$deriv) {
            return;
        }

        if (!isset($this->derivs[$deriv])) {
            $this->derivs[$deriv] = array();
        }
        $this->derivs[$deriv][] = $name;
    }

    public function addInterface($name, $file, $deriv)
    {
        $this->interfaces[$name] = array(
            "file"  => $file,
            "deriv" => $deriv,
        );
    }

    public function addFunction($name, $file)
    {
        $this->functions[$name] = array(
            "file"  => $file,
        );
    }

    public function symbolCount()
    {
        return count($this->classes) + count($this->interfaces) + count($this->functions);
    }

    public function toSymbolMap()
    {
        $classImpls = array();
        foreach ($this->classes as $className => $classArr) {
            if ($classArr["impls"]) {
                foreach ($classArr["impls"] as $interface) {
                    if (!isset($classImpls[$interface])) {
                        $classImpls[$interface] = array();
                    }

                    $classImpls[$interface][] = $className;
                }
            }
        }

        // We're only resolving one level for now
        foreach ($this->derivs as $baseName => $derivs) {
            foreach ($derivs as $deriv) {
                if ($deriv && isset($this->derivs[$deriv])) {
                    $this->derivs[$baseName] = array_merge($this->derivs[$baseName], $this->derivs[$deriv]);
                }
            }
        }

        $classes = array();
        foreach ($this->classes as $className => &$classArr) {
            $classArr = array_filter($classArr);
        }

        $symbolMap = array(
            "classes" => $this->classes,
            "functions" => ipull($this->functions, "file"),
            "xmap" => $this->derivs,
            "implementations" => $classImpls,
        );

        return $symbolMap;
    }
}
