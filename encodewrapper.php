<?php
require_once('HTMLPurifier.standalone.php');
require_once('htmlencode.php');

class EncodeWrapper {

    public  $htmlpurifier;
    private $htmlencode;
    private $self;

    public function encodePreChars($sourceStr) {
        if ($this->_self == false) return $this->htmlpurifier->purify($sourceStr);
        return $this->_htmlencode->encodePreChars($sourceStr);
    }

    public function encodePostChars($sourceStr) {
        if ($this->_self == false) return $this->htmlpurifier->purify($sourceStr);
        return $this->_htmlencode->encodePostChars($sourceStr);
    }

    public function __construct($encoder = 'HTMLEncode') {

        if ($encoder == 'HTMLPurifier') {
            $this->_self = false;

            $config = HTMLPurifier_Config::createDefault();
            $config->set('Core.Encoding', 'UTF-8');
            $config->set('Cache.SerializerPath', '/tmp');
            $this->htmlpurifier = new HTMLPurifier($config);

            return;
        }

        $this->_self = true;
        $this->_htmlencode = new HTMLEncode(false);
    }

    public function addBlackListedTag ($tagname) {
        if ($this->_self == false) return false;
        return $this->_htmlencode->addBlackListedTag($tagname);
    }

    public function removeBlackListedTag ($tagname) {
        if ($this->_self == false) return false;
        return $this->_htmlencode->removeBlackListedTag($tagname);
    }

    public function addJSFunction ($name) {
        if ($this->_self == false) return false;
        $this->_htmlencode->addJSFunction($name);
    }

    public function removeJSFunction ($name) {
        if ($this->_self == false) return false;
        return $this->_htmlencode->removeJSFunction($name);
    }

    public function addPreMapChar ($char, $rep_char) {
        if ($this->_self == false) return false;
        return $this->_htmlencode->addPreMapChar($char, $rep_char);
    }

    public function removePreMapChar ($char) {
        if ($this->_self == false) return false;
        return $this->_htmlencode->removePreMapChar($char);
    }

    public function addPostMapChar ($char, $rep_char) {
        if ($this->_self == false) return false;
        $this->_htmlencode->addPostMapChar($char, $rep_char);
    }

    public function removePostMapChar ($char) {
        if ($this->_self == false) return false;
        return $this->_htmlencode->removePostMapChar($char);
    }

    public function FilterJSFunctionCalls ($sourceStr)  {
        if ($this->_self == false) return $this->htmlpurifier->purify($sourceStr);
        return $this->_htmlencode->FilterJSFunctionCalls($sourceStr);
    }

    public function EncodeAndFilterBlackListedTags($sourceStr) {
        if ($this->_self == false) return $this->htmlpurifier->purify($sourceStr);
        return $this->_htmlencode->EncodeAndFilterBlackListedTags($sourceStr);
    }

    public function removeXSSForComment($sourceStr) {
        if ($this->_self == false) return $this->htmlpurifier->purify($sourceStr);
        return $this->_htmlencode->removeXSSForComment($sourceStr);
    }

    public function purify ($sourceStr) {
        if ($this->_self == false) return $this->htmlpurifier->purify($sourceStr);
        return $this->_htmlencode->purify($sourceStr);
    }
}
?>
