<?php
/*
Plugin Name: AnyVar
Plugin URI: http://www.devspace.com.au/anyvar/
Description: Easily add simple, changable variables (text snippets) anywhere you like - templates, widgets, posts, pages, links etc.
Author: Matt Swanson
Version: 0.1.1
Author URI: http://www.devspace.com.au/

--------------------------------------------------------------
0.1.0		2008-06-30		Initial release
0.1.1		2008-06-30		Updates to readme

*/ 


class AnyVar
{
	public $min_level = 7; // Minimum user level required to see the admin page.
	public $vars = array(); // The array that stores the variable > replacement text key/value pairs
	
	// Contructor
	function AnyVar()
	{
		// Load current vars
		$this->vars = get_option('anyvar_vars');
		
		if(is_admin())
		{
			add_action('admin_menu',array(&$this,'anyvar_menu'));
			add_action('activate_'.basename (dirname (__FILE__)).'/'.basename (__FILE__), array(&$this,'activate'));
		}
		else
		{
			add_action('template_redirect',array(&$this,'anyvar_ob_start'));
		}
	}
	
	/* Admin Display Functions */
	
	// Adds the AnyVar menu item
	function anyvar_menu()
	{
		$page = add_management_page('AnyVar Admin', 'AnyVar', $this->min_level, __FILE__, array(&$this,'anyvar_admin'));
		add_action("admin_print_scripts-$page", array(&$this,'anyvar_head')); // Add required JS file(s)
	}
	
	// Loads the Wordpress forms.js script
	function anyvar_head()
	{
		wp_enqueue_script('admin-forms');
	}
	
	// Displays the admin pages
	function anyvar_admin()
	{
		$action = '';
		if(isset($_REQUEST['action']))
			$action = $_REQUEST['action'];
		if ( isset($_POST['deleteit']) && isset($_POST['delete']) )
			$action = 'delete';
		
		$this->display_heading();
		
		// Strip the magic / wordpress added quotes
		$_POST['var_name'] = stripslashes(strtolower($_POST['var_name']));
		$_POST['var_text'] = stripslashes($_POST['var_text']);
		
		switch ($action)
		{
			case 'add': // The add variable form has been submitted
				$error = $this->validate($_POST['var_name']);
				if($error == '')
				{
					$this->vars[$_POST['var_name']] = $_POST['var_text'];
					update_option('anyvar_vars',$this->vars);
					$this->display_message("[{$_POST['var_name']}] successfully added.");
					$this->display_list();
					$this->display_form();
				}
				else // There's an error. Display the error and the form again
				{
					$this->display_message($error);
					$this->display_form('add',$_POST['var_name'],$_POST['var_text']);
				}
				break;
			
			case 'edit': // Load the variable to be edited, display it in a form
				if(isset($this->vars[$_GET['var']]))
				{
					$this->display_form('edit',$_GET['var'],$this->vars[$_GET['var']]);
				}
				else
				{
					$this->display_message("Cannot edit variable because it doesn't exist.");
					$this->display_list();
					$this->display_form();
				}
				break;
			
			case 'edited': // The edit variable form has been submitted
				$error = $this->validate($_POST['var_name']);
				if($error == '')
				{
					// Update the exiting key instead of unsetting and replacing it. This preserves the array order
					$temp_array = $this->update_array_key($this->vars,$_POST['old_var_name'],$_POST['var_name']);
					$temp_array[$_POST['var_name']] = $_POST['var_text'];
					$this->vars = $temp_array;
					update_option('anyvar_vars',$this->vars);
					unset($temp_array);
					$this->display_message("[{$_POST['var_name']}] successfully edited.");
					$this->display_list();
					$this->display_form();
				}
				else  // There's an error. Display the error and the form again
				{
					$this->display_message($error);
					$this->display_form('edit',$_POST['var_name'],$_POST['var_text']);
				}
				break;
			
			case 'delete': // Variable deletion form has been submitted
				if(is_array($_POST['delete']) && count($_POST['delete'])) // There are variables selected to be delete
				{
					$count = 0;
					foreach($_POST['delete'] as $unlucky_var)
					{
						if(isset($this->vars[$unlucky_var]))
						{
							$count++;
							unset($this->vars[$unlucky_var]);
						}
					}
					update_option('anyvar_vars',$this->vars);
					$this->display_message("$count variables deleted");
					$this->display_list();
					$this->display_form();
				}
				else // No variables were selected for deletion
				{
					$this->display_list();
					$this->display_form();
				}
				break;
				
			default: // The standard page
				$this->display_list();
				$this->display_form();
				break;
		}
	}
	
	// Displays a message
	function display_message($message)
	{
		echo "<div class='wrap'><div class='tablenav'><span style='font-weight:bold;'>$message</span></div></div><br class='clear' />";
	}
	
	// Displays the heading
	function display_heading()
	{
		?>
		<div class="wrap">
		<h2>AnyVar - Manage Variables</h2>
		</div>
		<?php
	}
	
	// Displays a table of the current variables
	function display_list()
	{
		?>
		<div class="wrap">
		<form id="anyvar-filter" action="?page=<?php echo $_GET['page'];?>" method="post">
		<div class="tablenav">
		<div class="alignleft"><input type="submit" value="Delete" name="deleteit" class="button-secondary delete" /></div>
		<br class="clear" />
		</div>

		<br class="clear" />

		<table class="widefat">
			<thead>
			<tr>
				<th scope="col" class="check-column"><input type="checkbox" onclick="checkAll(document.getElementById('anyvar-filter'));" />&nbsp;</th>
		        <th scope="col">Var Name</th>
		        <th scope="col">Var Tag</th>
		        <th scope="col">Var Text</th>
			</tr>
			</thead>
			<tbody id="the-list" class="list:cat">
		<?php
		// Loop through each existing variable
		$i = 0;
		foreach($this->vars as $var_name => $var_text)
		{
			$i++;
			if($i % 2 == 1)
				$class = "class='alternate'";
			else
				$class = '';
			echo "<tr id='anyvar-$var_name' $class>
				<th scope='row' class='check-column'><input type='checkbox' name='delete[]' value='$var_name' /></th>
				<td><a class='row-title' href='?page=".$_GET['page']."&action=edit&amp;var=$var_name' title='Edit &quot;$var_name&quot;'> $var_name</a></td>
				<td>[$var_name]</td>
				<td><textarea name='anyvar_text_$var_name' id='anyvar_text_$var_name' cols='60' rows='3' readonly>$var_text</textarea></td>
				</tr>";
		}
		?>
			</tbody>
		</table>
		</form>
		</div>
		
		<div class='wrap'>
		
		<p>The most effective way to invoke your variables is with tags.<br />
		Simply put a [var_name] tag anywhere in your HTML.<br />
		The other option is to use php: &lt;?php anyvar('var_name');&gt;</p>
		<p>Please Note: tags must be lowercase.</p>
		</div>
		
		<br class="clear" />
		<?php
	}
	
	// Displays the add / edit
	function display_form($action = '',$var_name = '',$var_text = '') // If $action = edit the edit variable form is displayed, otherwise it's the add variable form.
	{
		?>
		<div class="wrap">
		<h2><?php echo ($action == 'edit') ? 'Edit' : 'Add';?> A Variable</h2>
		<form name="addvar" id="addvar" method="post" action="?page=<?php echo $_GET['page'];?>" class="add:the-list: validate"><input type="hidden" name="action" value="<?php echo ($action == 'edit') ? 'edited' : 'add';?>" />
		<?php if($action == 'edit') echo '<input type="hidden" name="var_old_name" value="'.$var_name.'">';?>
		<table class="form-table">
			<tr class="form-field form-required">
					<th scope="row" valign="top"><label for="var_name">Variable Name</label></th>
					<td><input name="var_name" id="var_name" type="text" value="<?php echo $var_name;?>" size="40" /><br />

			        The variable name is used in variables tags, which are used to 'call' the variable. It may only contain lowercase letters, numbers and underscores.</td>
				</tr>
				<tr class="form-field">
					<th scope="row" valign="top"><label for="category_description">Variable Text</label></th>

					<td><textarea name="var_text" id="var_text" rows="5" cols="50" style="width: 97%;"><?php echo $var_text;?></textarea><br />
			        This is the text that'll be 'pasted' in where ever you put the variable tag.</td>
				</tr>
			</table>
		<p class="submit"><input type="submit" class="button" name="submit" value="<?php echo ($action == 'edit') ? 'Edit' : 'Add';?> Variable" /></p>
		</form>
		</div>
		<?php
	}
	

	/* Admin Functions */
	
	// Validate a variable name. Return an empty string if it's valid.
	function validate($var_name)
	{
		$error_str = '';
		if(strlen($var_name) < 1)
			$error_str .= 'ERROR: Variable Name cannot be left blank<br />';
		elseif(!preg_match('/^[a-z0-9_]*$/', $var_name))
			$error_str .= 'ERROR: Variable Name can only contain lowercase letters, numbers &amp; underscores (a-z,0-9,_)<br />';
		return $error_str;
	}
	
	// Update / change the value of a given array key
	function update_array_key($array,$old_key,$new_key)
	{
		foreach($array as $key => $value)
			$new_array[ ($key === $old_key) ? $new_key : $key ] = $value;
		return $new_array;
	}
	
	// Add the AnyVar variables array to Wordpress options
	function activate()
	{
		if(!is_array($this->vars))
		{
			$this->vars = array('example_var' => 'Example text. Delete this variable.');
			add_option('anyvar_vars',$this->vars);
		}
	}
	
	
	/* Output functions */
	
	// Start output buffering
	function anyvar_ob_start()
	{
		ob_start(array(&$this,'anyvar_callback'));
	}
	
	// Replace all existings variable tags with their text values
	function anyvar_callback($output_html)
	{
		$find = array_keys($this->vars);
		array_walk($find,create_function('&$value,$key', '$value = \'[\'.$value.\']\';'));
		return str_replace($find,array_values($this->vars),$output_html);
	}
	
	// Return the value of a variable
	function return_single_var($var_name)
	{
		if(isset($this->vars[$var_name]))
			return $this->vars[$var_name];
		return '';
	}
}

$anyvar = new AnyVar;

// Fnction for outputting an AnyVar variable via PHP. This is mainly useful in templates.
function anyvar($var_name)
{
	global $anyvar;
	echo $anyvar->return_single_var($var_name);
}
	
?>
