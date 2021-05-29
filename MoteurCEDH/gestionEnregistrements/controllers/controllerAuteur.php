<?php

/*
 * Example PHP implementation used for the index.html example
 */

// DataTables PHP library
include( "../lib/DataTables.php" );

// Alias Editor classes so they are easy to use
use
	DataTables\Editor,
	DataTables\Editor\Field,
	DataTables\Editor\Format,
	DataTables\Editor\Mjoin,
	DataTables\Editor\Options,
	DataTables\Editor\Upload,
	DataTables\Editor\Validate,
	DataTables\Editor\ValidateOptions;


// Build our Editor instance and process the data coming from _POST
Editor::inst( $db, 'auteur' )
	->fields(
		Field::inst( 'nom' ),
		Field::inst( 'prenom' )
	)
	->validator( function ( $editor, $action, $data ) {
        if ( $action === Editor::ACTION_CREATE || $action === Editor::ACTION_EDIT ) {
            foreach ( $data['data'] as $pkey => $values ) {
				$count = $editor->db()->query('select')->get('*')->table('auteur')
				->where('nom', $values['nom'])
				->where('prenom', $values['prenom'])
				->exec()
				->count();
				if ($count == 1){
					return 'La sélection a déjà été enregistrée.';
				}
			} 
		}
	} )
	->process( $_POST )
	->json();
