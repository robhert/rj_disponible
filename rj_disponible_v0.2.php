<?php

// This is a PLUGIN TEMPLATE for Textpattern CMS.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Plugin names should start with a three letter prefix which is
// unique and reserved for each plugin author ("abc" is just an example).
// Uncomment and edit this line to override:
$plugin['name'] = 'rj_disponible';

// Allow raw HTML help, as opposed to Textile.
// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '0.2';
$plugin['author'] = 'Robhert Pimentel';
$plugin['author_uri'] = 'pimentel.pe';
$plugin['description'] = 'Añade un interruptor entre estar o no disponible.';

// Plugin load order:
// The default value of 5 would fit most plugins, while for instance comment
// spam evaluators or URL redirectors would probably want to run earlier
// (1...4) to prepare the environment for everything else that follows.
// Values 6...9 should be considered for plugins which would work late.
// This order is user-overrideable.
$plugin['order'] = '5';

// Plugin 'type' defines where the plugin is loaded
// 0 = public              : only on the public side of the website (default)
// 1 = public+admin        : on both the public and admin side
// 2 = library             : only when include_plugin() or require_plugin() is called
// 3 = admin               : only on the admin side (no AJAX)
// 4 = admin+ajax          : only on the admin side (AJAX supported)
// 5 = public+admin+ajax   : on both the public and admin side (AJAX supported)
$plugin['type'] = '1';

// Plugin "flags" signal the presence of optional capabilities to the core plugin loader.
// Use an appropriately OR-ed combination of these flags.
// The four high-order bits 0xf000 are available for this plugin's private use
if (!defined('PLUGIN_HAS_PREFS')) define('PLUGIN_HAS_PREFS', 0x0001); // This plugin wants to receive "plugin_prefs.{$plugin['name']}" events
if (!defined('PLUGIN_LIFECYCLE_NOTIFY')) define('PLUGIN_LIFECYCLE_NOTIFY', 0x0002); // This plugin wants to receive "plugin_lifecycle.{$plugin['name']}" events

$plugin['flags'] = '1';

// Plugin 'textpack' is optional. It provides i18n strings to be used in conjunction with gTxt().
// Syntax:
// ## arbitrary comment
// #@event
// #@language ISO-LANGUAGE-CODE
// abc_string_name => Localized String

$plugin['textpack'] = <<<EOT
#@rj_disponible_estado
rj_disponible_estado => Disponibilidad
#@rj_disponible_estado
#@language en-us
rj_disponible_estado => Availability
EOT;

if (!defined('txpinterface'))
        @include_once('zem_tpl.php');

# --- BEGIN PLUGIN CODE ---
if (!defined('txpinterface')) {
    die('txpinterface is undefined.');
}

if (txpinterface === 'admin') {
    rj_disponible_install();
    register_callback('rj_disponible_lifecycle', 'plugin_lifecycle.rj_disponible');
}

function rj_disponible_install()
{
    $textpack = <<<TEXTPACK
#@language en, en-gb, en-us
rj_disponible_estado => Availability

#@language es, es-es
rj_disponible_estado => Disponibilidad
TEXTPACK;

    install_textpack($textpack);

    if (get_pref('rj_disponible_estado', null) === null) {
        set_pref(
            'rj_disponible_estado',
            '1',
            'site',
            PREF_PLUGIN,
            'rj_disponible_select',
            50
        );
    }
}

function rj_disponible_lifecycle($event, $step)
{
    if ($step === 'deleted') {
        safe_delete('txp_prefs', "name LIKE 'rj_disponible%'");
        safe_delete('txp_lang', "name LIKE 'rj_disponible%'");
    }
}

function rj_disponible_select($name, $val)
{
    return yesnoRadio($name, $val);
}

function rj_disponible_textpack()
{
    return <<<TEXTPACK
#@language en, en-gb, en-us
rj_disponible_estado => Availability

#@language es, es-es
rj_disponible_estado => Disponibilidad
TEXTPACK;
}

// Frontend
if (txpinterface === 'public') {
    Txp::get('\Textpattern\Tag\Registry')->register('rj_disponible');
}

function rj_disponible($atts, $thing = null)
{
    extract(lAtts(array(
        'class' => 'status',
        'lang'  => LANG,
    ), $atts));

    // Traducciones directas
    $textos = array(
        'es' => array(
            'si' => 'Disponible para proyectos',
            'no' => 'No disponible'
        ),
        'en' => array(
            'si' => 'Available for projects',
            'no' => 'Not available'
        )
    );

    // Detectar idioma base (es-es -> es)
    $lang_base = substr($lang, 0, 2);
    
    // Si no existe el idioma, usar inglés por defecto
    if (!isset($textos[$lang_base])) {
        $lang_base = 'en';
    }

    $estado = get_pref('rj_disponible_estado', '1');

    if ($estado === '1') {
        return '<div class="'.$class.'">'.$textos[$lang_base]['si'].'</div>';
    }
    
    return '<div class="'.$class.' no">'.$textos[$lang_base]['no'].'</div>';
}
# --- END PLUGIN CODE ---
if (0) {
?>
<!--
# --- BEGIN PLUGIN HELP ---

# --- END PLUGIN HELP ---
-->
<?php
}
?>