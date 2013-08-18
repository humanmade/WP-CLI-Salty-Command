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

		// @todo check that the project exists

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

}
WP_CLI::add_command( "salty", "WP_CLI_Salty_Command" );