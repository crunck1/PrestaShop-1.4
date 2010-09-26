<?php

/**
  * Generator tab for admin panel, AdminGenerator.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.3
  *
  */

include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');

class AdminGenerator extends AdminTab
{
	public function __construct()
	{
		$this->_path =  dirname(__FILE__).'/../../';
		$this->_htFile = $this->_path.'.htaccess';
		$this->_rbFile = $this->_path.'robots.txt';
		$this->_smFile = $this->_path.'sitemap.xml';
		$this->_smFileName = 'sitemap.xml';
		$this->_htData = $this->_getHtaccessContent();
		$this->_rbData = $this->_getRobotsContent();
		return parent::__construct();
	}

	public function display()
	{
		global $currentIndex;

		$languages = Language::getLanguages(false);

		// Htaccess
		echo '
		<form action="'.$currentIndex.'&token='.$this->token.'" method="post" enctype="multipart/form-data">
		<fieldset><legend><img src="../img/admin/htaccess.gif" />'.$this->l('Htaccess file generation').'</legend>
		<p><b>'.$this->l('Warning:').'</b> '.$this->l('this tool can ONLY be used if you are hosted by an Apache web server. Please ask your webhost.').'</p>
		<p>'.$this->l('This tool will automatically generate a ".htaccess" file that will grant you the possibility to do URL rewriting and to catch 404 errors.').'</p>
		<p>'.$this->l('If you do not have the "Friendly URL" enabled when generating the ".htaccess" file, such feature won\'t be available.').'</p>';
		if ($this->_checkConfiguration($this->_htFile))
			echo '
			<div class="clear">&nbsp;</div>
			<label for="imageCacheControl">'.$this->l('Optimization').'</label>
			<div class="margin-form">
				<input type="checkbox" name="cacheControl" id="cacheControl" '.(Tools::getValue('cacheControl') ? 'checked="checked"' : '').' />
				<p>'.$this->l('This will add directives to your .htaccess file which should improved cache and compression.').'</p>
			</div>
			<div class="clear">&nbsp;</div>
			<label for="webservice">'.$this->l('Webservice').'</label>
			<div class="margin-form">
				<input type="checkbox" name="webservice" id="webservice" '.(Tools::getValue('webservice') ? 'checked="checked"' : '').' />
				<p>'.$this->l('This will add directives to your .htaccess file which should allow webservice calls. Please that you have to set "friendly URL" to "Yes" in the "Preferences" tab in Back Office.').'</p>
			</div>
			<label for="specific_configuration">'.$this->l('Specific configuration:').'</label>
			<div class="margin-form">
				<textarea rows="10" class="width3" id="specific_configuration" name="ps_htaccess_specific">'.Configuration::get('PS_HTACCESS_SPECIFIC').'</textarea>
				<p>'.$this->l('Add here the specifical directives of your hosting (SetEnv PHP_VER 5, AddType x-mapp-php5 .php...).').'</p>
			</div>
			<p class="clear" style="font-weight:bold;">'.$this->l('Generate your ".htaccess" file by clicking on the following button:').'<br /><br />
			<input type="submit" value="'.$this->l('Generate .htaccess file').'" name="submitHtaccess" class="button" /></p>
			<p>'.$this->l('This will erase your').'<b> '.$this->l('old').'</b> '.$this->l('.htaccess file!').'</p>';
		else
			echo '
			<p style="color:red; font-weight:bold;">'.$this->l('Before being able to use this tool, you need to:').'</p>
			<p>'.$this->l('- create a').' <b>'. $this->l('.htaccess').'</b> '.$this->l('blank file in dir:').' <b>'.__PS_BASE_URI__.'</b>
			<br />'.$this->l('- give it write permissions (CHMOD 666 on Unix system)').'</p>';
		echo '</p></fieldset></form>';

		// Robots
		echo '<br /><br />
		<form action="'.$currentIndex.'&token='.$this->token.'" method="post" enctype="multipart/form-data">
		<fieldset><legend><img src="../img/admin/robots.gif" />'.$this->l('Robots file generation').'</legend>
		<p><b>'.$this->l('Warning:').' </b>'.$this->l('Your file robots.txt MUST be in your website\'s root dir and nowhere else.').'</p>
		<p>'.$this->l('eg: http://www.yoursite.com/robots.txt').'.</p>
		<p>'.$this->l('This tool will automatically generate a "robots.txt" file that will grant you the possibility to deny access to search engines for somes pages.').'</p>';
		if ($this->_checkConfiguration($this->_rbFile))
			echo '
			<p style="font-weight:bold;">'.$this->l('Generate your "robots.txt" file by clicking on the following button:').'<br /><br />
			<input type="submit" value="'.$this->l('Generate robots.txt file').'" name="submitRobots" class="button" /></p>
			<p>'.$this->l('This will erase your').'<b> '.$this->l('old').'</b> '.$this->l('robots.txt file!').'</p>';
		else
			echo '
			<p style="color:red; font-weight:bold;">'.$this->l('Before being able to use this tool, you need to:').'</p>
			<p>'.$this->l('- create a').' <b>'. $this->l('robots.txt').'</b> '.$this->l('blank file in dir:').' <b>'.__PS_BASE_URI__.'</b>
			<br />'.$this->l('- give it write permissions (CHMOD 666 on Unix system)').'</p>';
		echo '</p></fieldset></form>';
	}

	public function _checkConfiguration($file)
	{
		$ret = file_exists($file);
		$ret &= is_writable($file);
		return $ret;
	}

	function postProcess()
	{
		global $currentIndex;

		if (Tools::isSubmit('submitHtaccess'))
		{
			if ($this->tabAccess['edit'] === '1')
			{
				if (!$writeFd = @fopen($this->_htFile, 'w'))
					die ($this->l('Cannot write into file:').' <b>'.$this->_htFile.'</b><br />'.$this->l('Please check write permissions.'));
				else
				{
					
					Configuration::updateValue('PS_HTACCESS_SPECIFIC',  Tools::getValue('ps_htaccess_specific'));
					
					// PS Comments
					fwrite($writeFd, "# .htaccess automaticaly generated by PrestaShop e-commerce open-source solution\n");
					fwrite($writeFd, "# http://www.prestashop.com - http://www.prestashop.com/forums\n\n");
					if ($specific = Configuration::get('PS_HTACCESS_SPECIFIC'))
						fwrite($writeFd, $specific);
					// RewriteEngine
					if (Configuration::get('PS_REWRITING_SETTINGS'))
					{
						fwrite($writeFd, "\n<IfModule mod_rewrite.c>\n");
						fwrite($writeFd, $this->_htData['RewriteEngine']['comment']."\nRewriteEngine on\n\n");
						fwrite($writeFd, $this->_htData['RewriteRule']['comment']."\n");
						foreach ($this->_htData['RewriteRule']['content'] as $rule => $url)
							fwrite($writeFd, 'RewriteRule '.$rule.' '.__PS_BASE_URI__.$url."\n");
						fwrite($writeFd, "</IfModule>\n\n");
					}
					
					// ErrorDocument
					fwrite($writeFd, $this->_htData['ErrorDocument']['comment']."\nErrorDocument ".$this->_htData['ErrorDocument']['content']."\n");
					
					// Cache control
					if (Tools::getValue('cacheControl'))
					{
						$cacheControl = "
<IfModule mod_expires.c>
		ExpiresActive On
		ExpiresByType image/gif \"access plus 1 month\"
		ExpiresByType image/jpeg \"access plus 1 month\"
		ExpiresByType image/png \"access plus 1 month\"
		ExpiresByType text/css \"access plus 1 week\"
		ExpiresByType text/javascript \"access plus 1 week\"
		ExpiresByType application/javascript \"access plus 1 week\"
		ExpiresByType application/x-javascript \"access plus 1 week\"
		ExpiresByType image/x-icon \"access plus 1 year\"
</IfModule>

FileETag INode MTime Size
<IfModule mod_deflate.c>
	AddOutputFilterByType DEFLATE text/html
	AddOutputFilterByType DEFLATE text/css
	AddOutputFilterByType DEFLATE text/javascript
	AddOutputFilterByType DEFLATE application/javascript
	AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>
";
						fwrite($writeFd, $cacheControl);
					}

					fclose($writeFd);
					Tools::redirectAdmin($currentIndex.'&conf=4&token='.$this->token);
				}
			} else
				$this->_errors[] = Tools::displayError('You do not have permission to edit anything here.');
		}

		if (Tools::isSubmit('submitRobots'))
		{
			if ($this->tabAccess['edit'] === '1')
			{
				if (!$writeFd = @fopen($this->_rbFile, 'w'))
					die ($this->l('Cannot write into file:').' <b>'.$this->_rbFile.'</b><br />'.$this->l('Please check write permissions.'));
				else
				{
					// PS Comments
					fwrite($writeFd, "# robots.txt automaticaly generated by PrestaShop e-commerce open-source solution\n");
					fwrite($writeFd, "# http://www.prestashop.com - http://www.prestashop.com/forums\n\n");
					fwrite($writeFd, "# This file is to prevent the crawling and indexing of certain parts\n");
					fwrite($writeFd, "# of your site by web crawlers and spiders run by sites like Yahoo!\n");
					fwrite($writeFd, "# and Google. By telling these \"robots\" where not to go on your site,\n");
					fwrite($writeFd, "# you save bandwidth and server resources.\n\n");
					fwrite($writeFd, "# For more information about the robots.txt standard, see:\n");
					fwrite($writeFd, "# http://www.robotstxt.org/wc/robots.html\n\n");

					// User-Agent
					fwrite($writeFd, "User-agent: *\n\n");

					// Directories
					fwrite($writeFd, "# Directories\n");
					foreach ($this->_rbData['Directories'] as $dir)
						fwrite($writeFd, 'Disallow: '.__PS_BASE_URI__.$dir."\n");
					fwrite($writeFd, "\n");

					// Files
					fwrite($writeFd, "# Files\n");
					foreach ($this->_rbData['Files'] as $file)
						fwrite($writeFd, 'Disallow: '.__PS_BASE_URI__.$file."\n");
					fwrite($writeFd, "\n");

					// Sitemap
					fwrite($writeFd, "# Sitemap\n");
					if (file_exists($this->_smFile))
						if (filesize($this->_smFile))
							fwrite($writeFd, 'Sitemap: '.(Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').$_SERVER['SERVER_NAME'].__PS_BASE_URI__.$this->_smFileName."\n");
					fwrite($writeFd, "\n");

					fclose($writeFd);
					Tools::redirectAdmin($currentIndex.'&conf=4&token='.$this->token);
				}
			} else
				$this->_errors[] = Tools::displayError('You do not have permission to edit anything here.');
		}
	}

	public function _getHtaccessContent()
	{
		$tab = array('ErrorDocument' => array(), 'RewriteEngine' => array(), 'RewriteRule' => array());

		// ErrorDocument
		$tab['ErrorDocument']['comment'] = '# Catch 404 errors';
		$tab['ErrorDocument']['content'] = '404 '.__PS_BASE_URI__.'404.php';
		$tab['ErrorDocument']['comment'] = '# Catch 404 errors';
		$tab['ErrorDocument']['content'] = '404 '.__PS_BASE_URI__.'404.php';

		// RewriteEngine
		$tab['RewriteEngine']['comment'] = '# URL rewriting module activation';

		// RewriteRules
		//IMPORTANT : if you change the lines bellow, don"t forget to change the "urlcanonical" module too
		$tab['RewriteRule']['comment'] = '# URL rewriting rules';
		$tab['RewriteRule']['content']['^([a-z0-9]+)\-([a-z0-9]+)(\-[_a-zA-Z0-9-]*)/([_a-zA-Z0-9-]*)\.jpg$'] = 'img/p/$1-$2$3.jpg [L,E]';
		$tab['RewriteRule']['content']['^([0-9]+)\-([0-9]+)/([_a-zA-Z0-9-]*)\.jpg$'] = 'img/p/$1-$2.jpg [L,E]';
		$tab['RewriteRule']['content']['^([0-9]+)(\-[_a-zA-Z0-9-]*)/([_a-zA-Z0-9-]*)\.jpg$'] = 'img/c/$1$2.jpg [L,E]';
		$tab['RewriteRule']['content']['^lang-([a-z]{2})/([a-zA-Z0-9-]*)/([0-9]+)\-([a-zA-Z0-9-]*)\.html(.*)$'] = 'product.php?id_product=$3&isolang=$1$5 [L,E]';
		$tab['RewriteRule']['content']['^lang-([a-z]{2})/([0-9]+)\-([a-zA-Z0-9-]*)\.html(.*)$'] = 'product.php?id_product=$2&isolang=$1$4 [QSA,L,E]';
		$tab['RewriteRule']['content']['^lang-([a-z]{2})/([0-9]+)\-([a-zA-Z0-9-]*)(.*)$'] = 'category.php?id_category=$2&isolang=$1 [QSA,L,E]';
		$tab['RewriteRule']['content']['^([a-zA-Z0-9-]*)/([0-9]+)\-([a-zA-Z0-9-]*)\.html(.*)$'] = 'product.php?id_product=$2$4 [QSA,L,E]';
		$tab['RewriteRule']['content']['^([0-9]+)\-([a-zA-Z0-9-]*)\.html(.*)$'] = 'product.php?id_product=$1$3 [QSA,L,E]';
		$tab['RewriteRule']['content']['^([0-9]+)\-([a-zA-Z0-9-]*)(.*)$'] = 'category.php?id_category=$1 [QSA,L,E]';
		$tab['RewriteRule']['content']['^content/([0-9]+)\-([a-zA-Z0-9-]*)(.*)$'] = 'cms.php?id_cms=$1 [QSA,L,E]';
		$tab['RewriteRule']['content']['^([0-9]+)__([a-zA-Z0-9-]*)(.*)$'] = 'supplier.php?id_supplier=$1$3 [QSA,L,E]';
		$tab['RewriteRule']['content']['^([0-9]+)_([a-zA-Z0-9-]*)(.*)$'] = 'manufacturer.php?id_manufacturer=$1$3 [QSA,L,E]';
		$tab['RewriteRule']['content']['^lang-([a-z]{2})/(.*)$'] = '$2?isolang=$1 [QSA,L,E]';
		// Webservice
		if (Tools::getValue('webservice'))
			$tab['RewriteRule']['content']['^api/?(.*)$'] = 'tools/webservice/dispatcher.php?url=$1 [QSA,L,E]';

		return $tab;
	}

	public function _getRobotsContent()
	{
		$tab = array();

		
		$lang_dir = 'lang-'.Language::getIsoById(Configuration::get('PS_LANG_DEFAULT')).'/';
		
		// Directories
		$tab['Directories'] = array('classes/', 'config/', 'download/', 'mails/', 'modules/', 'translations/', 'tools/', $lang_dir);

		// Files
		$tab['Files'] = array('addresses.php', 'address.php', 'authentication.php', 'cart.php', 'discount.php', 'footer.php',
		'get-file.php', 'header.php', 'history.php', 'identity.php', 'images.inc.php', 'init.php', 'my-account.php', 'order.php',
		'order-slip.php', 'order-detail.php', 'order-follow.php', 'order-return.php', 'order-confirmation.php', 'pagination.php', 'password.php',
		'pdf-invoice.php', 'pdf-order-return.php', 'pdf-order-slip.php', 'product-sort.php', 'search.php', 'statistics.php');

		return $tab;
	}
}

?>
