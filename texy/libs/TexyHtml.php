<?php
if (!class_exists('Texy', FALSE)) die();
class TexyHtml implements ArrayAccess
{
    public $name;
    public $attrs = array();
    public $children;
    public $isEmpty;
    static public $xhtml = TRUE;
    static private $replacedTags = array('br'=>1,'button'=>1,'iframe'=>1,'img'=>1,'input'=>1,
        'object'=>1,'script'=>1,'select'=>1,'textarea'=>1,'applet'=>1,'embed'=>1,'canvas'=>1);
    static public $emptyTags = array('img'=>1,'hr'=>1,'br'=>1,'input'=>1,'meta'=>1,'area'=>1,
        'base'=>1,'col'=>1,'link'=>1,'param'=>1,'basefont'=>1,'frame'=>1,'isindex'=>1,'wbr'=>1,'embed'=>1);
    static public function el($name=NULL, $attrs=NULL)
    {
        $el = new self;
        if ($name !== NULL)
            $el->setName($name);
        if ($attrs !== NULL) {
            if (!is_array($attrs))
                throw new Exception('Attributes must be array');
            $el->attrs = $attrs;
        }
        return $el;
    }
    static public function text($text)
    {
        $el = new self;
        $el->setText($text);
        return $el;
    }
    public function setName($name)
    {
        if ($name !== NULL && !is_string($name))
            throw new Exception('Name must be string or NULL');
        $this->name = $name;
        $this->isEmpty = isset(self::$emptyTags[$name]);
        return $this;
    }
    public function setText($text)
    {
        if ($text === NULL)
            $text = '';
        elseif (!is_scalar($text))
            throw new Exception('Content must be scalar');
        $this->children = $text;
        return $this;
    }
    public function getText()
    {
        if (is_array($this->children)) return FALSE;
        return $this->children;
    }
    public function addChild(TexyHtml $child)
    {
        $this->children[] = $child;
        return $this;
    }
    public function getChild($index)
    {
        if (isset($this->children[$index]))
            return $this->children[$index];
        return NULL;
    }
    public function add($name, $text=NULL)
    {
        $child = new self;
        $child->setName($name);
        if ($text !== NULL) $child->setText($text);
        return $this->children[] = $child;
    }
    public function href($path, $query=NULL)
    {
        if ($query) {
            $query = http_build_query($query, NULL, '&');
            if ($query !== '') $path .= '?' . $query;
        }
        $this->attrs['href'] = $path;
        return $this;
    }
    public function offsetGet($i)
    {
        if (isset($this->attrs[$i])) {
            if (is_array($this->attrs[$i]))
                $this->attrs[$i] = new ArrayObject($this->attrs[$i]);
            return $this->attrs[$i];
        }
        if ($i === 'style' || $i === 'class') {
            return $this->attrs[$i] = new ArrayObject;
        }
        return NULL;
    }
    public function offsetSet($i, $value)
    {
        if ($i === NULL) throw new Exception('Invalid TexyHtml usage.');
        $this->attrs[$i] = $value;
    }
    public function offsetExists($i)
    {
        return isset($this->attrs[$i]);
    }
    public function offsetUnset($i)
    {
        unset($this->attrs[$i]);
    }
    public function export($texy)
    {
        $ct = $this->getContentType();
        $s = $texy->protect($this->startTag(), $ct);
        if ($this->isEmpty) return $s;
        if (is_array($this->children)) {
            foreach ($this->children as $val)
                $s .= $val->export($texy);
        } else {
            $s .= $this->children;
        }
        return $s . $texy->protect($this->endTag(), $ct);
    }
    public function startTag()
    {
        if (!$this->name) return '';
        $s = '<' . $this->name;
        if (is_array($this->attrs))
        foreach ($this->attrs as $key => $value)
        {
            if ($value === NULL || $value === FALSE) continue;
            if ($value === TRUE) {
                if (self::$xhtml) $s .= ' ' . $key . '="' . $key . '"';
                else $s .= ' ' . $key;
                continue;
            } elseif (is_array($value) || is_object($value)) {
                $tmp = NULL;
                foreach ($value as $k => $v) {
                    if ($v == NULL) continue;
                    if (is_string($k)) $tmp[] = $k . ':' . $v;
                    else $tmp[] = $v;
                }
                if (!$tmp) continue;
                $value = implode($key === 'style' ? ';' : ' ', $tmp);
            } elseif ($key === 'href' && substr($value, 0, 7) === 'mailto:') {
                $tmp = '';
                for ($i=0; $i<strlen($value); $i++) $tmp .= '&#' . ord($value[$i]) . ';'; // WARNING: no utf support
                $s .= ' href="' . $tmp . '"';
                continue;
            }
            $value = str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $value);
            $s .= ' ' . $key . '="' . Texy::freezeSpaces($value) . '"';
        }
        if (self::$xhtml && $this->isEmpty) return $s . ' />';
        return $s . '>';
    }
    public function endTag()
    {
        if ($this->name && !$this->isEmpty)
            return '</' . $this->name . '>';
        return '';
    }
    public function isTextual()
    {
        return !$this->isEmpty && is_scalar($this->children);
    }
    public function __clone()
    {
        if (is_array($this->children)) {
            foreach ($this->children as $key => $val)
                $this->children[$key] = clone $val;
        }
    }
    public function getContentType()
    {
        if (isset(self::$replacedTags[$this->name])) return Texy::CONTENT_REPLACED;
        if (isset(TexyHtmlCleaner::$inline[$this->name])) return Texy::CONTENT_MARKUP;
        return Texy::CONTENT_BLOCK;
    }
    public function parseLine($texy, $s)
    {
        $s = str_replace(array('\)', '\*'), array('&#x29;', '&#x2A;'), $s);
        $parser = new TexyLineParser($texy, $this);
        $parser->parse($s);
    }
    public function parseBlock($texy, $s, $topLevel=FALSE)
    {
        $parser = new TexyBlockParser($texy, $this);
        $parser->topLevel = $topLevel;
        $parser->parse($s);
    }
    function __get($nm) { throw new Exception("Undefined property '" . get_class($this) . "::$$nm'"); }
    function __set($nm, $val) { $this->__get($nm); }
}
