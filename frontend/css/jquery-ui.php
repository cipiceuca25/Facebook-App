<?php
header("Content-type: text/css");

$primary = "#FFF";
$secondary = "#33669";
$tertiary = "#900";
?>
@charset "UTF-8";

/*
 * jQuery UI CSS Framework 1.8.18
 *
 * Copyright 2011, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI/Theming/API
 */

/* Layout helpers
----------------------------------*/
.ui-helper-hidden { display: none; }
.ui-helper-hidden-accessible { position: absolute !important; clip: rect(1px 1px 1px 1px); clip: rect(1px,1px,1px,1px); }
.ui-helper-reset { margin: 0; padding: 0; border: 0; outline: 0; line-height: 1.3; text-decoration: none; font-size: 100%; list-style: none; }
.ui-helper-clearfix:before, .ui-helper-clearfix:after { content: ""; display: table; }
.ui-helper-clearfix:after { clear: both; }
.ui-helper-clearfix { zoom: 1; }
.ui-helper-zfix { width: 100%; height: 100%; top: 0; left: 0; position: absolute; opacity: 0; filter:Alpha(Opacity=0); }


/* Interaction Cues
----------------------------------*/
.ui-state-disabled { cursor: default !important; }


/* Misc visuals
----------------------------------*/

/* Overlays */
.ui-widget-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }


/*
 * jQuery UI CSS Framework 1.8.18
 *
 * Copyright 2011, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI/Theming/API
 *
 */


/* Component containers
----------------------------------*/
.ui-widget { font-family: Arial,Verdana,sans-serif; font-size: 1em; background:<?php echo $kevin; ?>; }
.ui-widget .ui-widget { font-size: 1em; }
.ui-widget input, .ui-widget select, .ui-widget textarea, .ui-widget button { font-family: Verdana,Arial,sans-serif; font-size: 1em; }
.ui-widget-content { color: #222222; }
.ui-widget-content a { color: #222222; }
.ui-widget-header { border-bottom: 1px solid #aaaaaa; color: #222222; font-weight: bold; }
.ui-widget-header a { color: #222222; }

/* Interaction states
----------------------------------*/
.ui-state-default, .ui-widget-content .ui-state-default, .ui-widget-header .ui-state-default { border: 1px solid #d3d3d3; background: #e6e6e6; font-weight: normal; color: #555555; }
.ui-state-default a, .ui-state-default a:link, .ui-state-default a:visited { color: #555555; text-decoration: none; }
.ui-state-hover, .ui-widget-content .ui-state-hover, .ui-widget-header .ui-state-hover, .ui-state-focus, .ui-widget-content .ui-state-focus, .ui-widget-header .ui-state-focus { border: 1px solid #999999; background: #dadada; font-weight: normal; color: #212121; }
.ui-state-hover a, .ui-state-hover a:hover { color: #212121; text-decoration: none; }
.ui-state-active, .ui-widget-content .ui-state-active, .ui-widget-header .ui-state-active { border: 1px solid #aaaaaa; background: #ffffff; font-weight: normal; color: #212121; }
.ui-state-active a, .ui-state-active a:link, .ui-state-active a:visited { color: #212121; text-decoration: none; }
.ui-widget :active { outline: none; }

/* Interaction Cues
----------------------------------*/
.ui-state-highlight, .ui-widget-content .ui-state-highlight, .ui-widget-header .ui-state-highlight  {border: 1px solid #fcefa1; background: #fbf9ee; color: #363636; }
.ui-state-highlight a, .ui-widget-content .ui-state-highlight a,.ui-widget-header .ui-state-highlight a { color: #363636; }
.ui-state-error, .ui-widget-content .ui-state-error, .ui-widget-header .ui-state-error {border: 1px solid #cd0a0a; background: #fef1ec; color: #cd0a0a; }
.ui-state-error a, .ui-widget-content .ui-state-error a, .ui-widget-header .ui-state-error a { color: #cd0a0a; }
.ui-state-error-text, .ui-widget-content .ui-state-error-text, .ui-widget-header .ui-state-error-text { color: #cd0a0a; }
.ui-priority-primary, .ui-widget-content .ui-priority-primary, .ui-widget-header .ui-priority-primary { font-weight: bold; }
.ui-priority-secondary, .ui-widget-content .ui-priority-secondary,  .ui-widget-header .ui-priority-secondary { opacity: .7; filter:Alpha(Opacity=70); font-weight: normal; }
.ui-state-disabled, .ui-widget-content .ui-state-disabled, .ui-widget-header .ui-state-disabled { opacity: .35; filter:Alpha(Opacity=35); background-image: none; }

/* Misc visuals
----------------------------------*/

/* Overlays */
.ui-widget-overlay { background: #aaaaaa; opacity: .30;filter:Alpha(Opacity=30); }
.ui-widget-shadow { margin: -8px 0 0 -8px; padding: 8px; background: #aaaaaa; opacity: .30;filter:Alpha(Opacity=30); -moz-border-radius: 8px; -khtml-border-radius: 8px; -webkit-border-radius: 8px; border-radius: 8px; }/*
 * jQuery UI Resizable 1.8.18
 *
 * Copyright 2011, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI/Resizable#theming
 */
.ui-resizable { position: relative;}
.ui-resizable-handle { position: absolute;font-size: 0.1px;z-index: 99999; display: block; }
.ui-resizable-disabled .ui-resizable-handle, .ui-resizable-autohide .ui-resizable-handle { display: none; }
.ui-resizable-n { cursor: n-resize; height: 7px; width: 100%; top: -5px; left: 0; }
.ui-resizable-s { cursor: s-resize; height: 7px; width: 100%; bottom: -5px; left: 0; }
.ui-resizable-e { cursor: e-resize; width: 7px; right: -5px; top: 0; height: 100%; }
.ui-resizable-w { cursor: w-resize; width: 7px; left: -5px; top: 0; height: 100%; }
.ui-resizable-se { cursor: se-resize; width: 12px; height: 12px; right: 1px; bottom: 1px; }
.ui-resizable-sw { cursor: sw-resize; width: 9px; height: 9px; left: -5px; bottom: -5px; }
.ui-resizable-nw { cursor: nw-resize; width: 9px; height: 9px; left: -5px; top: -5px; }
.ui-resizable-ne { cursor: ne-resize; width: 9px; height: 9px; right: -5px; top: -5px;}/*
 * jQuery UI Selectable 1.8.18
 *
 * Copyright 2011, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI/Selectable#theming
 */
.ui-selectable-helper { position: absolute; z-index: 100; border:1px dotted black; }
/*
 * jQuery UI Accordion 1.8.18
 *
 * Copyright 2011, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI/Accordion#theming
 */
/* IE/Win - Fix animation bug - #4615 */
.ui-accordion { width: 100%; }
.ui-accordion .ui-accordion-header { cursor: pointer; position: relative; margin-top: 1px; zoom: 1; }
.ui-accordion .ui-accordion-li-fix { display: inline; }
.ui-accordion .ui-accordion-header-active { border-bottom: 0 !important; }
.ui-accordion .ui-accordion-header a { display: block; font-size: 1em; padding: .5em .5em .5em .7em; }
.ui-accordion-icons .ui-accordion-header a { padding-left: 2.2em; }
.ui-accordion .ui-accordion-header .ui-icon { position: absolute; left: .5em; top: 50%; margin-top: -8px; }
.ui-accordion .ui-accordion-content { padding: 1em 2.2em; border-top: 0; margin-top: -2px; position: relative; top: 1px; margin-bottom: 2px; overflow: auto; display: none; zoom: 1; }
.ui-accordion .ui-accordion-content-active { display: block; }
/*
 * jQuery UI Autocomplete 1.8.18
 *
 * Copyright 2011, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI/Autocomplete#theming
 */
.ui-autocomplete { position: absolute; cursor: default; }	

/* workarounds */
* html .ui-autocomplete { width:1px; } /* without this, the menu expands to 100% in IE6 */

/*
 * jQuery UI Menu 1.8.18
 *
 * Copyright 2010, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI/Menu#theming
 */
.ui-menu {
	list-style:none;
	padding: 2px;
	margin: 0;
	display:block;
	float: left;
}
.ui-menu .ui-menu {
	margin-top: -3px;
}
.ui-menu .ui-menu-item {
	margin:0;
	padding: 0;
	zoom: 1;
	float: left;
	clear: left;
	width: 100%;
}
.ui-menu .ui-menu-item a {
	text-decoration:none;
	display:block;
	padding:.2em .4em;
	line-height:1.5;
	zoom:1;
}
.ui-menu .ui-menu-item a.ui-state-hover,
.ui-menu .ui-menu-item a.ui-state-active {
	font-weight: normal;
	margin: -1px;
}
/*
 * jQuery UI Dialog 1.8.18
 *
 * Copyright 2011, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI/Dialog#theming
 */
.ui-dialog { position: absolute; padding: .2em; width: 300px; overflow: hidden; }
.ui-dialog .ui-dialog-titlebar { padding: .4em 1em; position: relative;  }
.ui-dialog .ui-dialog-title { float: left; margin: .1em 16px .1em 0; } 
.ui-dialog .ui-dialog-titlebar-close { position: absolute; right: .3em; top: 50%; width: 19px; margin: -10px 0 0 0; padding: 1px; height: 18px; }
.ui-dialog .ui-dialog-titlebar-close span { display: block; margin: 1px; }
.ui-dialog .ui-dialog-titlebar-close:hover, .ui-dialog .ui-dialog-titlebar-close:focus { padding: 0; }
.ui-dialog .ui-dialog-content { position: relative; border: 0; padding: .5em 1em; background: none; overflow: auto; zoom: 1; }
.ui-dialog .ui-dialog-buttonpane { text-align: left; border-width: 1px 0 0 0; background-image: none; margin: .5em 0 0 0; padding: .3em 1em .5em .4em; }
.ui-dialog .ui-dialog-buttonpane .ui-dialog-buttonset { float: right; }
.ui-dialog .ui-dialog-buttonpane button { margin: .5em .4em .5em 0; cursor: pointer; }
.ui-dialog .ui-resizable-se { width: 14px; height: 14px; right: 3px; bottom: 3px; }
.ui-draggable .ui-dialog-titlebar { cursor: move; }
/*
 * jQuery UI Slider 1.8.18
 *
 * Copyright 2011, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI/Slider#theming
 */
.ui-slider { position: relative; text-align: left; }
.ui-slider .ui-slider-handle { position: absolute; z-index: 2; width: 1.2em; height: 1.2em; cursor: default; }
.ui-slider .ui-slider-range { position: absolute; z-index: 1; font-size: .7em; display: block; border: 0; background-position: 0 0; }

.ui-slider-horizontal { height: .8em; }
.ui-slider-horizontal .ui-slider-handle { top: -.3em; margin-left: -.6em; }
.ui-slider-horizontal .ui-slider-range { top: 0; height: 100%; }
.ui-slider-horizontal .ui-slider-range-min { left: 0; }
.ui-slider-horizontal .ui-slider-range-max { right: 0; }

.ui-slider-vertical { width: .8em; height: 100px; }
.ui-slider-vertical .ui-slider-handle { left: -.3em; margin-left: 0; margin-bottom: -.6em; }
.ui-slider-vertical .ui-slider-range { left: 0; width: 100%; }
.ui-slider-vertical .ui-slider-range-min { bottom: 0; }
.ui-slider-vertical .ui-slider-range-max { top: 0; }/*
 * jQuery UI Tabs 1.8.18
 *
 * Copyright 2011, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI/Tabs#theming
 */
.ui-tabs { position: relative; zoom: 1; } /* position: relative prevents IE scroll bug (element with position: relative inside container with overflow: auto appear as "fixed") */
.ui-tabs .ui-tabs-nav { margin: 0; padding: .2em .2em 0; }
.ui-tabs .ui-tabs-nav li { list-style: none; float: left; position: relative; top: 1px; margin: 0 .2em 1px 0; border-bottom: 0 !important; padding: 0; white-space: nowrap; }
.ui-tabs .ui-tabs-nav li a { float: left; padding: .5em 1em; text-decoration: none; }
.ui-tabs .ui-tabs-nav li.ui-tabs-selected { margin-bottom: 0; padding-bottom: 1px; }
.ui-tabs .ui-tabs-nav li.ui-tabs-selected a, .ui-tabs .ui-tabs-nav li.ui-state-disabled a, .ui-tabs .ui-tabs-nav li.ui-state-processing a { cursor: text; }
.ui-tabs .ui-tabs-nav li a, .ui-tabs.ui-tabs-collapsible .ui-tabs-nav li.ui-tabs-selected a { cursor: pointer; } /* first selector in group seems obsolete, but required to overcome bug in Opera applying cursor: text overall if defined elsewhere... */
.ui-tabs .ui-tabs-panel { display: block; border-width: 0; padding: 10px; background: none; }
.ui-tabs .ui-tabs-hide { display: none !important; }
/*
 * jQuery UI Datepicker 1.8.18
 *
 * Copyright 2011, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI/Datepicker#theming
 */
.ui-datepicker { width: 17em; padding: .2em .2em 0; display: none; }
.ui-datepicker .ui-datepicker-header { position:relative; padding:.2em 0; }
.ui-datepicker .ui-datepicker-prev, .ui-datepicker .ui-datepicker-next { position:absolute; top: 2px; width: 1.8em; height: 1.8em; }
.ui-datepicker .ui-datepicker-prev-hover, .ui-datepicker .ui-datepicker-next-hover { top: 1px; }
.ui-datepicker .ui-datepicker-prev { left:2px; }
.ui-datepicker .ui-datepicker-next { right:2px; }
.ui-datepicker .ui-datepicker-prev-hover { left:1px; }
.ui-datepicker .ui-datepicker-next-hover { right:1px; }
.ui-datepicker .ui-datepicker-prev span, .ui-datepicker .ui-datepicker-next span { display: block; position: absolute; left: 50%; margin-left: -8px; top: 50%; margin-top: -8px;  }
.ui-datepicker .ui-datepicker-title { margin: 0 2.3em; line-height: 1.8em; text-align: center; }
.ui-datepicker .ui-datepicker-title select { font-size:1em; margin:1px 0; }
.ui-datepicker select.ui-datepicker-month-year {width: 100%;}
.ui-datepicker select.ui-datepicker-month, 
.ui-datepicker select.ui-datepicker-year { width: 49%;}
.ui-datepicker table {width: 100%; font-size: .9em; border-collapse: collapse; margin:0 0 .4em; }
.ui-datepicker th { padding: .7em .3em; text-align: center; font-weight: bold; border: 0;  }
.ui-datepicker td { border: 0; padding: 1px; }
.ui-datepicker td span, .ui-datepicker td a { display: block; padding: .2em; text-align: right; text-decoration: none; }
.ui-datepicker .ui-datepicker-buttonpane { background-image: none; margin: .7em 0 0 0; padding:0 .2em; border-left: 0; border-right: 0; border-bottom: 0; }
.ui-datepicker .ui-datepicker-buttonpane button { float: right; margin: .5em .2em .4em; cursor: pointer; padding: .2em .6em .3em .6em; width:auto; overflow:visible; }
.ui-datepicker .ui-datepicker-buttonpane button.ui-datepicker-current { float:left; }

/* with multiple calendars */
.ui-datepicker.ui-datepicker-multi { width:auto; }
.ui-datepicker-multi .ui-datepicker-group { float:left; }
.ui-datepicker-multi .ui-datepicker-group table { width:95%; margin:0 auto .4em; }
.ui-datepicker-multi-2 .ui-datepicker-group { width:50%; }
.ui-datepicker-multi-3 .ui-datepicker-group { width:33.3%; }
.ui-datepicker-multi-4 .ui-datepicker-group { width:25%; }
.ui-datepicker-multi .ui-datepicker-group-last .ui-datepicker-header { border-left-width:0; }
.ui-datepicker-multi .ui-datepicker-group-middle .ui-datepicker-header { border-left-width:0; }
.ui-datepicker-multi .ui-datepicker-buttonpane { clear:left; }
.ui-datepicker-row-break { clear:both; width:100%; font-size:0em; }

/* RTL support */
.ui-datepicker-rtl { direction: rtl; }
.ui-datepicker-rtl .ui-datepicker-prev { right: 2px; left: auto; }
.ui-datepicker-rtl .ui-datepicker-next { left: 2px; right: auto; }
.ui-datepicker-rtl .ui-datepicker-prev:hover { right: 1px; left: auto; }
.ui-datepicker-rtl .ui-datepicker-next:hover { left: 1px; right: auto; }
.ui-datepicker-rtl .ui-datepicker-buttonpane { clear:right; }
.ui-datepicker-rtl .ui-datepicker-buttonpane button { float: left; }
.ui-datepicker-rtl .ui-datepicker-buttonpane button.ui-datepicker-current { float:right; }
.ui-datepicker-rtl .ui-datepicker-group { float:right; }
.ui-datepicker-rtl .ui-datepicker-group-last .ui-datepicker-header { border-right-width:0; border-left-width:1px; }
.ui-datepicker-rtl .ui-datepicker-group-middle .ui-datepicker-header { border-right-width:0; border-left-width:1px; }

/* IE6 IFRAME FIX (taken from datepicker 1.5.3 */
.ui-datepicker-cover {
    display: none; /*sorry for IE5*/
    display/**/: block; /*sorry for IE5*/
    position: absolute; /*must have*/
    z-index: -1; /*must have*/
    filter: mask(); /*must have*/
    top: -4px; /*must have*/
    left: -4px; /*must have*/
    width: 200px; /*must have*/
    height: 200px; /*must have*/
}/*
 * jQuery UI Progressbar 1.8.18
 *
 * Copyright 2011, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI/Progressbar#theming
 */
.ui-progressbar { height:2em; text-align: left; overflow: hidden; }
.ui-progressbar .ui-progressbar-value {margin: -1px; height:100%; }