<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>

<ul id="subsubmenu">
   <li><a href="<?php echo $strUrl; ?>&sub=list"<?php if( 'list' == $_GET['sub'] ) echo ' class="current"'; ?>>List</a></li>
   <li><a href="<?php echo $strUrl; ?>&sub=add"<?php if( 'add' == $_GET['sub'] ) echo ' class="current"'; ?>>Add new</a></li>
   <li><a href="<?php echo $strUrl; ?>&sub=options"<?php if( 'options' == $_GET['sub'] ) echo ' class="current"'; ?>>Options</a></li>
</ul>
