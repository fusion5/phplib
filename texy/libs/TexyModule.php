<?php
if (!class_exists('Texy', FALSE)) die();
abstract class TexyModule
{
    protected $texy;
    protected $default = array();
    public function __construct($texy)
    {
        $this->texy = $texy;
        $texy->registerModule($this);
        $texy->allowed = array_merge($texy->allowed, $this->default);
    }
    public function begin()
    {}
    function __get($nm) { throw new Exception("Undefined property '" . get_class($this) . "::$$nm'"); }
    function __set($nm, $val) { $this->__get($nm); }
} // TexyModule
interface ITexyPreBlock
{
    public function preBlock($block, $topLevel);
}
interface ITexyPostLine
{
    public function postLine($line);
}
