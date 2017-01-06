<?php
/**
 * Manage projects in Salty WordPress
 * 
 * @when before_wp_load
 */
class WP_CLI_Salty_Command extends WP_CLI_Command {

	/**
	 * Deploy a project.
	 *
	 * @subcommand deploy
	 * @synopsis <project>
	 */
	public function deploy( $args, $assoc_args ) {

		WP_CLI::success( "Project deployed." );
	}

	/**
	 * Initialize a project.
	 * 
	 * @when before_wp_load
	 * 
	 * @subcommand init
	 * @synopsis <project>
	 */
	public function init( $args, $assoc_args ) {

		list( $unsafe_project ) = $args;

		// Equivalent of WordPress' sanitize_key()
		$unsafe_project = strtolower( $unsafe_project );
		$project = preg_replace( '/[^a-z0-9_\-]/', '', $unsafe_project );

		// Check that the <project>.sls exists
		$project_sls = $this->get_project_sls_file_path( $project );
		if ( ! file_exists( $project_sls ) ) {
			WP_CLI::out( "Project sls file doesn't exist." );
			$config = $this->get_config();
			if ( empty( $config['salt-master'] ) || empty( $config['ssh-user'] ) ) {
				WP_CLI::line();
				WP_CLI::error( "Please create a config.yml and specify 'salt-master' and 'ssh-user'" );
			}
			WP_CLI::line( " Attempting to fetch from Salt master..." );

			$cmd = sprintf( "ssh %s@%s 'salt-run hmn.get_project %s'", escapeshellarg( $config['ssh-user'] ), escapeshellarg( $config['salt-master'] ), $project );
			$project_sls_contents = shell_exec( $cmd );
			file_put_contents( $project_sls, $project_sls_contents );
			WP_CLI::line( "Created local project sls file." );
		} else {
			WP_CLI::line( "Found project sls file." );
		}

		// @todo clone the SLS file and run Salt state.highstate --local

		// Connect to MySQL
		$mysqli = new mysqli( 'localhost', 'root', '' );
		if ( $mysqli->connect_error )
			WP_CLI::error( $mysqli->connect_error );

		// Create the database if it doesn't exist
		if ( ! $mysqli->select_db( $project ) ) {
			if ( $mysqli->query( "CREATE DATABASE {$project}" ) )
				WP_CLI::line( "Created database." );
			else
				WP_CLI::error( "Error creating database." );
		} else {
			WP_CLI::line( "Database already exists." );
		}

		$mysqli->close();

		// @todo symlink wp-config-local.php if one doesn't exist

		// @todo install WordPress if it isn't installed

		WP_CLI::success( "Project initialized." );
	}

	/**
	 * Get the filepath for a <project>.sls file
	 */
	private function get_project_sls_file_path( $project ) {
		$projects_dir = '/srv/salt/projects';
		if ( ! is_dir( $projects_dir ) )
			WP_CLI::error( "Project sls directory doesn't exist." );
		return $projects_dir . '/' . $project . '.sls';
	}

	/**
	 * Get the config details for this Salty WordPress
	 */
	private function get_config() {
		static $config;

		if ( isset( $config ) )
			return $config;

		$config_file = '/vagrant/config.yml';
		if ( file_exists( $config_file ) )
			$config = spyc_load_file( $config_file );
		else
			$config = array();
		return $config;
	}

}
WP_CLI::add_command( "salty", "WP_CLI_Salty_Command" );