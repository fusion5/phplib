<?php
if (!class_exists('Texy', FALSE)) die();
class TexyScriptModule extends TexyModule
{
    protected $default = array('script' => TRUE);
    public $handler;
    public $separator = ',';
    public function begin()
    {
        $this->texy->registerLinePattern(
            array($this, 'pattern'),
            '#\{\{([^'.TEXY_MARK.']+)\}\}()#U',
            'script'
        );
    }
    public function pattern($parser, $matches)
    {
        list(, $mContent) = $matches;
        $cmd = trim($mContent);
        if ($cmd === '') return FALSE;
        $args = $raw = NULL;
        if (preg_match('#^([a-z_][a-z0-9_-]*)\s*(?:\(([^()]*)\)|:(.*))$#iu', $cmd, $matches)) {
            $cmd = $matches[1];
            $raw = isset($matches[3]) ? trim($matches[3]) : trim($matches[2]);
            if ($raw === '')
                $args = array();
            else
                $args = preg_split('#\s*' . preg_quote($this->separator, '#') . '\s*#u', $raw);
        }
        if ($this->handler) {
            if (is_callable(array($this->handler, $cmd))) {
                array_unshift($args, $parser);
                return call_user_func_array(array($this->handler, $cmd), $args);
            }
            if (is_callable($this->handler))
                return call_user_func_array($this->handler, array($parser, $cmd, $args, $raw));
        }
        if (is_callable(array($this->texy->handler, 'script'))) {
            $res = $this->texy->handler->script($parser, $cmd, $args, $raw);
            if ($res !== Texy::PROCEED) return $res;
        }
        if ($cmd==='texy')
            return $this->texyHandler($args);
        return FALSE;
    }
    public function texyHandler($args)
    {
        return '';
    }
} // TexyScriptModule
