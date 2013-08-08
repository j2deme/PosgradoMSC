<?php
class PHP_Check
{
    protected $_test;

    function __construct()
    {
        $this->_test = array();
    }

    function addSection($section)
    {
        $this->_test[$section] = array();
        return $this;
    }

    function addResult($section, PHP_Check_Result $result)
    {
        $this->_test[$section][] = $result;
        return $this;
    }

    function createResult($section, $result, $message)
    {
        $resultObject = new PHP_Check_Result();
        $resultObject->message = $message;
        $resultObject->result  = $result;
        $this->addResult($section, $resultObject);
        return $this;
    }

    function getTest()
    {
        return $this->_test;
    }

    function toHtml()
    {
        $html = '';
        foreach ($this->_test as $sectionTitle => $sectionResults) {
            $html .= $this->_formatSection($sectionTitle);
            if (count($sectionResults)) {
                $html .= $this->_formatResults($sectionResults);
            }
        }
        return $html;
    }

    protected function _formatSection($section)
    {
        $id = str_replace(' ', '_', strtolower($section));
        return sprintf("<h2 id=\"%s\">%s</h2>\n", $id, $section);
    }

    protected function _formatResults($results)
    {
        $html = '';
        $html .= '<ul>';
        foreach ($results as $result) {
            if ($result->result) {
                $class = 'alert-success';
            } else {
                $class = 'alert-error';
            }
            $html .= sprintf("<li class=\"alert %s\">%s</li>\n", $class, $result->message);
        }
        $html .= "</ul>\n";
        return $html;
    }
}

class PHP_Check_Result
{
    var $result;
    var $message;
}

function check_keepalive($phpinfo)
{
    return check_createresult(
        strpos($phpinfo, 'Keep Alive: off'),
        'Keep Alive is off',
        'Keep Alive should be off'
    );
}

function check_apachemodule($phpinfo, $module)
{
    return check_createresult(
        strpos($phpinfo, $module),
        "<b>$module</b> esta habilitado",
        "<b>$module</b> esta deshabilitado"
    );
}

function check_phpversion()
{
    return check_createresult(
        version_compare(PHP_VERSION, '5.2.13') >= 0,
        "PHP ".PHP_VERSION." esta instalado",
        "Se requiere PHP 5.2.13 o superior"
    );
}

function check_phpextension($extension)
{
    $extensions = get_loaded_extensions();
    return in_array($extension, $extensions);
}

function check_phprequiredextensions(PHP_Check $test)
{
    $required_extensions = array('curl', 'dom', 'gd', 'hash', 'iconv', 'PDO', 'pdo_mysql', 'imagick');
    foreach ($required_extensions as $extension) {
        $result = check_phprequiredextension($extension);
        $test->addResult('PHP', $result);
    }
}

function check_phprequiredextension($extension)
{
    return check_createresult(
        check_phpextension($extension),
        "La extension <b>$extension</b> esta instalada",
        "La extension <b>$extension</b> es necesaria"
    );
}

function has_git()
{
    exec('which git', $output);
    $git = file_exists($line = trim(current($output))) ? $line : 'git';
    unset($output);
    exec($git . ' --version', $output);
    preg_match('#^(git version)#', current($output), $matches);

    return check_createresult(
        !empty($matches[0]),
        "Git esta instalado",
        "Git no esta instalado"
    );
//    return ! empty($matches[0]) ? $git : false;
//    echo ! empty($matches[0]) ? 'installed' : 'nope';
}

function check_phpapc()
{
    return check_phprequiredextension('apc');
}

function check_phpini($ini, $recommended)
{
    return check_createresult(
        ini_get($ini) >= $recommended,
        "PHP configuration value <code>$ini</code> is <code>" . ini_get($ini) . "</code>",
        "PHP configuration value <code>$ini</code> should be <code>$recommended</code> or higher, currently: <code>" . ini_get($ini) . "</code>"
    );
}

function check_mysqlvar($mysqlVars, $key, $recommended, $megabyte = true)
{
    $value       = $mysqlVars[$key];
    $label       = $recommended;
    $actualLabel = $value;

    if ($megabyte) {
        $label       = check_mysqlbytestoconfig($recommended);
        $actualLabel = check_mysqlbytestoconfig($value);
    }

    return check_createresult(
        $value >= $recommended,
        "MySQL configuration value <code>$key</code> is <code>$actualLabel</code>",
        "MySQL configuration value <code>$key</code> should be <code>$label</code> or higher, currently: <code>" . $actualLabel . "</code>"
    );
}

function check_mysqlbytestoconfig($bytes)
{
    return $bytes / 1048576 . "M";
}

function check_mysqlconfigtobytes($config)
{
    return rtrim($config, 'Mm') * 1048576;
}

function check_createresult($test, $pass, $fail)
{
    $result = new PHP_Check_Result();
    $result->result = $test;
    if ($result->result) {
        $result->message = $pass;
    } else {
        $result->message = $fail;
    }
    return $result;
}

$test = new PHP_Check();


// Get phpinfo contents for Apache checks
ob_start();
phpinfo();
$phpinfo = ob_get_clean();

// Check Apache
$test->addSection('Apache');
//$test->addResult('Apache', check_keepalive($phpinfo));
$test->addResult('Apache', check_apachemodule($phpinfo, 'mod_deflate'));
$test->addResult('Apache', check_apachemodule($phpinfo, 'mod_dir'));
$test->addResult('Apache', check_apachemodule($phpinfo, 'mod_expires'));
$test->addResult('Apache', check_apachemodule($phpinfo, 'mod_headers'));
$test->addResult('Apache', check_apachemodule($phpinfo, 'mod_mime'));
$test->addResult('Apache', check_apachemodule($phpinfo, 'mod_rewrite'));

// Check PHP
$test->addSection('PHP');
$test->addResult('PHP', check_phpversion());
check_phprequiredextensions($test);

// Check APC
/*$test->addSection('PHP APC');
$test->addResult('PHP APC', check_phpapc());
if (check_phpextension('apc')) {
    $test->addResult('PHP APC', check_phpini('apc.shm_size', 256));
    $test->addResult('PHP APC', check_phpini('apc.num_files_hint', 10000));
    $test->addResult('PHP APC', check_phpini('apc.user_entries_hint', 10000));
    $test->addResult('PHP APC', check_phpini('apc.max_file_size', 5));
    $cache = apc_cache_info('opcode');
    $test->addResult('PHP APC', magecheck_createresult(
        $cache['expunges'] < 1,
        "APC expunges count is 0",
        "APC expunges count is greater than 0, consider adjusting your cache size"
    ));
}*/

// Check Git
$test->addSection('Git');
$test->addResult('Git',has_git());
?>
<html>
<head>
    <title><?php echo $_SERVER['HTTP_HOST'];?> - Server Health Check</title>
    <style type="text/css">
        body {
            font-family: sans-serif;
        }
        ul {
            padding-left: 0;
        }
        label, input {
            display: block;
            margin-bottom: 5px;
        }
        .alert {
            background-color: #FCF8E3;
            border: 1px solid #FBEED5;
            border-radius: 4px 4px 4px 4px;
            color: #C09853;
            margin-bottom: 20px;
            padding: 8px 35px 8px 14px;
            text-shadow: 0 1px 0 rgba(255, 255, 255, 0.5);
            list-style: none;
        }
        .alert-success {
            background-color: #DFF0D8;
            border-color: #D6E9C6;
            color: #468847;
        }
        .alert-error {
            background-color: #F2DEDE;
            border-color: #EED3D7;
            color: #B94A48;
        }
    </style>
</head>
<body>
<h1>Server Health Check</h1>
<?php echo $test->toHtml(); ?>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
<script>
$(document).ready(function() {
    if ($('#mysql').length) {
        var h2 = $('#mysql');
        h2.after($('#mysql-calculator').html());
    }
    if ($('#magento').length) {
        var h2 = $('#magento');
        h2.after($('#check-permissions').html());
    }
})
</script>
</body>
</html>
