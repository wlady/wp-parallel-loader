		<div class="wrap ">
			<div id="icon-options-general" class="icon32"><br /></div>
			<h2><?php _e( 'WP Parallel Loader' , 'wp-parallel-loader' ); ?></h2>
			<?php if ($message) : ?>
				<div class="updated <?php if ($error) echo 'error'; ?>">
					<p><?php _e($message); ?></p>
				</div>
			<?php endif; ?>
			<form action="" method="post">
				<h3><?php _e( 'Configured Hosts' , 'wp-parallel-loader' ); ?></h3>
				<table class="form-table">
				<?php foreach ($cdns as $key=>$host) : ?>
					<tr valign="top">
						<td width="30">
							<input type="checkbox" name="hosts[<?php echo $key; ?>]" value="<?php echo $host; ?>" />
						</td>
						<td scope="row">
							<?php echo $host; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</table>
				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e( 'Delete Selected Hosts' , 'wp-parallel-loader' ) ?>" />
				</p>
			</form>
			<form action="" method="post">
				<h3><?php _e( 'Add Host' , 'wp-parallel-loader' ); ?></h3>
				<p>Should be something like http://cdn1.example.com, http://cdn2.example.com...</p>
				<table class="form-table">
					<tr valign="top">
						<th scope="row">
							<?php _e( 'Host Name' , 'wp-parallel-loader' ); ?>
						</th>
						<td>
							<input type="text" name="add_host" id="add_host" value="http://" size="100" />
						</td>
					</tr>
				</table>
				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e( 'Add Host' , 'wp-parallel-loader' ) ?>" />
				</p>
			</form>
			<form action="options.php" method="post">
				<?php settings_fields ( 'wp-parallel-loader' ); ?>
				<h3><?php _e( 'WP Parallel Loader Processor Options' , 'wp-parallel-loader' ); ?></h3>
				<table class="form-table">
					<tr valign="top">
						<th scope="row">
							<?php _e( 'Images' , 'wp-parallel-loader-images' ); ?>
						</th>
						<td>
							<input type="checkbox" name="wp-parallel-loader[wp-parallel-loader-images]" value="1" <?php echo ($this->get_option('wp-parallel-loader-images') ? 'checked="checked"' : '' )?> />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<?php _e( 'Script Files' , 'wp-parallel-loader-scripts' ); ?>
						</th>
						<td>
							<input type="checkbox" name="wp-parallel-loader[wp-parallel-loader-scripts]" value="1" <?php echo ($this->get_option('wp-parallel-loader-scripts') ? 'checked="checked"' : '' )?> />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<?php _e( 'CSS Files' , 'wp-parallel-loader-css' ); ?>
						</th>
						<td>
							<input type="checkbox" name="wp-parallel-loader[wp-parallel-loader-css]" value="1" <?php echo ($this->get_option('wp-parallel-loader-css') ? 'checked="checked"' : '' )?> />
						</td>
					</tr>
				</table>

				<h3><?php _e( 'WP Parallel Loader Plugin Options' , 'wp-parallel-loader' ); ?></h3>
				<p><?php _e( 'Leave this field empty to disable check updates.' , 'wp-parallel-loader' ); ?></p>
				<table class="form-table">
					<tr valign="top">
						<th scope="row">
							<?php _e( 'Plugin Repository' , 'wp-parallel-loader-repository' ); ?>
						</th>
						<td>
							<input type="text" name="wp-parallel-loader[wp-parallel-loader-repository]" value="<?php echo $this->get_option ( 'wp-parallel-loader-repository' ); ?>" size="100" />
						</td>
					</tr>
				</table>

				<p class="submit">
					<input type="submit" name="wp-parallel-loader-submit" id="wp-parallel-loader-submit" class="button-primary" value="<?php _e( 'Save Changes' , 'wp-parallel-loader' ) ?>" />
					<input type="submit" name="wp-parallel-loader-defaults" id="wp-parallel-loader-defaults" class="button-primary" value="<?php _e( 'Reset to Defaults' , 'wp-parallel-loader' ) ?>" />
				</p>
			</form>
		</div>
