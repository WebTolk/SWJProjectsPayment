<?php
/**
 * @package    SW JProjects Payment
 * @version    __DEPLOY_VERSION__
 * @author     Septdir Workshop - www.septdir.com
 * @copyright  Copyright (c) 2018 - 2019 Septdir Workshop. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://www.septdir.com/
 */

defined('_JEXEC') or die;

?>
<h1>Thank you for your purchase</h1>
<dl>
	<dt>Project</dt>
	<dd><?php echo $project->title; ?></dd>
	<dt>Key</dt>
	<dd><?php echo $key->key; ?></dd>
</dl>
<div>
	<a href="<?php echo $downloadLink; ?>" class="btn btn-success">Download</a>
</div>