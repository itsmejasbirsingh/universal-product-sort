<?php
function ups_settings() {
    ?>
    <h2>Settings</h2>
    <div id="col-container">
	    <div id="col-left">
	    <form method="post">
	    	<p>Field name: <input type="text" name="field_name"/></p>
	    	<p>Description: <textarea name="description"></textarea></p>
	    	<p>Content: <input type="radio" name="content" checked value="text">Text <input type="radio" name="content" value="number">Number <input type="radio" name="content" value="range">Range</p>
	    	<p><input type="submit" value="Add New Field" name="add_new_field"></p>
	    	</form>
	    </div>
	    <div id="col-right">
	    	
	    </div>   	
    </div>
    <?php
}