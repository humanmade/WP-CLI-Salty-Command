<?php
/**
 * Manage projects in Salty WordPress
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
	 * @subcommand init
	 * @synopsis <project>
	 */
	public function init( $args, $assoc_args ) {

		WP_CLI::success( "Project initialized." );
	}

}
WP_CLI::add_command( "salty", "WP_CLI_Salty_Command" );