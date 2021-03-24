<div class='woonet-setup-wizard woonet-license-key'>
	<img src='<?php echo plugins_url( '/assets/images/connect.png' ,  dirname(dirname(__FILE__) ) ); ?>' alt='Lock'/>
	<form  autocomplete="off" action='#' method='GET' id='woonet-add-child-site'>
		<h1> Add a New Site </h1>
		<p> Please enter the URL of the child site you want to add to this network. </p>
		<div class="error notice" style='display: none;'></div> 
		<input type='text' value='' placeholder="Enter site URL">
		<button type='button' class='button-primary'> Add </button>
	</form>

	<form  style='display: none;' action='#' method='GET' id='woonet-copy-code-form'> 
		<h1> Connect Link </h2>
		<p> Enter the code below into the child site to connect both sites. </p>
		<textarea style='resize:none;' cols='90' rows='5' id='woonet-copy-code'></textarea>
	</form>
</div>