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
Editor::inst( $db, 'reference' )
    ->field(
        Field::inst( 'reference.libelle' ),
		Field::inst( 'reference.datedelarret' )
		->validator( Validate::dateFormat( 'Y-m-d' ) )
		->getFormatter( Format::dateSqlToFormat( 'Y-m-d' ) )
		->setFormatter( Format::dateFormatToSql('Y-m-d' ) ),
        Field::inst( 'reference.revue' ),
        Field::inst( 'reference.code_auteur' )->options( Options::inst()
		->table( 'auteur' )
		->value( 'id' )
		->label( 'nom' )
	),
		Field::inst( 'auteur.nom' ),
		Field::inst( 'reference.code_pays' )->options( Options::inst()
		->table( 'pays' )
		->value( 'id' )
		->label( 'libelle' )
	),
	Field::inst( 'pays.libelle' )
    )
	->leftJoin( 'auteur','auteur.id', '=', 'reference.code_auteur' )
	->leftJoin( 'pays','pays.id', '=', 'reference.code_pays' )
    ->process($_POST)
    ->json();


	