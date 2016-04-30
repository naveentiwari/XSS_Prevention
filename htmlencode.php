<?php

class HTMLEncode {

    private $blackListedTags = Array();
    private $charPreMap = Array();
    private $charPostMap = Array();

    private $jsfunction = Array ('FSCommand', 'onAbort', 'onActivate', 'onAfterPrint', 'onAfterUpdate', 'onBeforeActivate', 'onBeforeCopy', 'onBeforeCut', 'onBeforeDeactivate', 'onBeforeEditFocus', 'onBeforePaste', 'onBeforePrint', 'onBeforeUnload', 'onBeforeUpdate', 'onBegin', 'onBlur', 'onBounce', 'onCellChange', 'onChange', 'onClick', 'onContextMenu', 'onControlSelect', 'onCopy', 'onCut', 'onDataAvailable', 'onDataSetChanged', 'onDataSetComplete', 'onDblClick', 'onDeactivate', 'onDrag', 'onDragEnd', 'onDragLeave', 'onDragEnter', 'onDragOver', 'onDragDrop', 'onDragStart', 'onDrop', 'onEnd', 'onError', 'onErrorUpdate', 'onFilterChange', 'onFinish', 'onFocus', 'onFocusIn', 'onFocusOut', 'onHashChange', 'onHelp', 'onInput', 'onKeyDown', 'onKeyPress', 'onKeyUp', 'onLayoutComplete', 'onLoad', 'onLoseCapture', 'onMediaComplete', 'onMediaError', 'onMessage', 'onMouseDown', 'onMouseEnter', 'onMouseLeave', 'onMouseMove', 'onMouseOut', 'onMouseOver', 'onMouseUp', 'onMouseWheel', 'onMove', 'onMoveEnd', 'onMoveStart', 'onOffline', 'onOnline', 'onOutOfSync', 'onPaste', 'onPause', 'onPopState', 'onProgress', 'onPropertyChange', 'onReadyStateChange', 'onRedo', 'onRepeat', 'onReset', 'onResize', 'onResizeEnd', 'onResizeStart', 'onResume', 'onReverse', 'onRowsEnter', 'onRowExit', 'onRowDelete', 'onRowInserted', 'onScroll', 'onSeek', 'onSelect', 'onSelectionChange', 'onSelectStart', 'onStart', 'onStop', 'onStorage', 'onSyncRestored', 'onSubmit', 'onTimeError', 'onTrackChange', 'onUndo', 'onUnload', 'onURLFlip', 'seekSegmentTime');

    public function encodePreChars($sourceStr) {
        foreach ($this->charPreMap as $key => $value) {
            $sourceStr = str_replace ($key, $value, $sourceStr);
        }

        return $sourceStr;
    }

    public function encodePostChars($sourceStr) {
        foreach ($this->charPostMap as $key => $value) {
            $sourceStr = str_replace ($key, $value, $sourceStr);
        }

        return $sourceStr;
    }

    public function __construct($empty = false) {

        // leave every thing empty, user is responsible for
        // filling values in the array
        if ($empty)
            return;

        $this->addBlackListedTag('script');
        $this->addBlackListedTag('img');

        $this->addPreMapChar('&', '&amp;');
        $this->addPreMapChar('"', '&quot;');
        $this->addPreMapChar("'", '&#x27;');

        $this->addPostMapChar('/', '&#x2F;');
    }

    public function addBlackListedTag ($tagname) {
        $tag_opn_find_emt   = '/<'.$tagname.'>/';
        $tag_opn_rep_emt    = '&lt;'.$tagname.'&gt;';
        $tag_opn_find       = '/<'.$tagname.'(?P<attr_info>[^>]*)>/';
        $tag_opn_rep        = '&lt;'.$tagname.'$1&gt;';
        $tag_close_find     = '/<\/'.$tagname.'>/';
        $tag_close_rep      = '&lt;/'.$tagname.'&gt;';

        $this->blackListedTags[$tag_opn_find_emt] = $tag_opn_rep_emt;
        $this->blackListedTags[$tag_opn_find]     = $tag_opn_rep;
        $this->blackListedTags[$tag_close_find]   = $tag_close_rep;
    }

    public function removeBlackListedTag ($tagname) {
        $tag_opn_find_emt   = '/<'.$tagname.'>/';
        $tag_opn_rep_emt    = '&lt;'.$tagname.'&gt;';
        $tag_opn_find       = '/<'.$tagname.'(?P<attr_info>[^>]*)>/';
        $tag_opn_rep        = '&lt;'.$tagname.'$1&gt;';
        $tag_close_find     = '/<\/'.$tagname.'>/';
        $tag_close_rep      = '&lt;/'.$tagname.'&gt;';

        unset($this->blackListedTags[$tag_opn_find_emt]);
        unset($this->blackListedTags[$tag_opn_find]);
        unset($this->blackListedTags[$tag_close_find]);
    }

    public function addJSFunction ($name) {
        $this->jsfunction[] = $name;
    }

    public function removeJSFunction ($name) {
        $ndx = array_search($name, $this->jsfunction);

        if (gettype($ndx) != 'boolean') {
            unset($this->jsfunction[$ndx]);
        }
    }

    public function addPreMapChar ($char, $rep_char) {
        $this->charPreMap[$char] = $rep_char;
    }

    public function removePreMapChar ($char) {
        unset($this->charPreMap[$char]);
    }

    public function addPostMapChar ($char, $rep_char) {
        $this->charPostMap[$char] = $rep_char;
    }

    public function removePostMapChar ($char) {
        unset($this->charPostMap[$char]);
    }

    public function FilterJSFunctionCalls ($sourceStr)  {
        foreach ($this->jsfunction as $key => $value) {
            //$pattern = '/(?P<oattr><.*)' . $value . '\s*=\s*[^\s>]*/i';
            $pattern = '/' . $value . '\s*=\s*[^\s>]*/i';
            $sourceStr = preg_replace($pattern, '$1', $sourceStr);
        }

        return $sourceStr;
    }

    public function EncodeAndFilterBlackListedTags($sourceStr) {
        // do the pre-encodeing of charactes
        $sourceStr = $this->EncodePreChars($sourceStr);
        foreach ($this->blackListedTags as $key => $value) {
            $sourceStr = preg_replace ($key, $value, $sourceStr);
        }

        // do the post-encoding of characters
        $sourceStr = $this->EncodePostChars ($sourceStr);

        return $sourceStr;
    }

    public function removeXSSForComment($sourceStr) {
        $sourceStr = str_replace('-->', '&#x2D;&#x2D;&gt;', $sourceStr);
        $sourceStr = str_replace('<!--', '&lt;&#x21;&#x2D;&#x2D;', $sourceStr);

        return $sourceStr;
    }

    public function purify ($sourceStr) {
        $sourceStr = $this->FilterJSFunctionCalls($sourceStr);
        $sourceStr = $this->removeXSSForComment ($sourceStr);
        $sourceStr = $this->EncodeAndFilterBlackListedTags($sourceStr);
        return $sourceStr;
    }
}
?>
