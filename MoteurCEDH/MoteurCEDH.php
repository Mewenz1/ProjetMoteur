<?php
/*
Plugin Name: Moteur CEDH
Description: Ceci est un plugin permettant d'ajouter un fichier CSV à la base de données et de gérer les enregistrements de celle-ci.
Author: Mewen Lucas
Version: 1.2
*/

add_action('admin_menu', 'ajout_plugin_menu');
 
function ajout_plugin_menu(){
    add_menu_page('MoteurCEDH', 'Moteur CEDH', 'manage_options', 'gestion', 'gestion','', 30);
   // add_submenu_page( 'ajout','Ajout de fichiers', 'Ajout de fichiers CSV', 'manage_options', 'ajout', 'form' );
   // add_submenu_page( 'ajout','Gestion des enregistrements', 'Gestion des enregistrements', 'manage_options', 'gestion', 'gestion' );
}

function form(){
    echo '<img src="/wordpress/wp-content/plugins/MoteurCEDH/exemple.png" style="float:right" height="700" alt="Exemple d\'enregistrement">
             <h1>Ajouter un fichier</h1>
            <form enctype="multipart/form-data" method="post">
            <input type="file" name="fichier" accept=".csv">
            <br>
            <input type="submit" value="Valider">
            </form>';
            echo 'Si le fichier Excel contient plusieurs fiches, veillez à enregistrer toutes les fiches sous différents fichiers CSV, car le CSV ne gère qu\'une fiche. <br>';
            echo 'Voici un exemple pour enregistrer un fichier Excel en fichier CSV. Sélectionnez bien CSV UTF-8, car les autres types CSV ne fonctionnent pas. <br> ';
            
    require_once 'database.php';

    $database = new Database();
    $db = $database->getConnection();

    if (isset($_FILES['fichier']))
    {  
        $tmp=$_FILES['fichier']['tmp_name'];
        $query='';
        $ro = 1;
        $doublon_arr=array();
        $ajout=0;
        $pattern = "/([-\s:\s/])/";

        if (($handle = fopen($tmp, "r")) !== FALSE) {

            $content = fgets($handle, 4096); // Lis la première ligne avec le titre des colonnes
            $begin = 'INSERT INTO test (requerantes,etatdefendeur,datedelarret,articles,thematique,auteur,revue,reference)';

            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                $num = count($data);
                $ro++;
                for ($c=0; $c < $num; $c++) {
                    $requerantes = $data[0];
                    $etatdefendeur = $data[1];
                    $datedelarret = $data[2];
                    $articles = $data[3];
                    $thematique = $data[4];
                    $auteur = $data[5];
                    $revue = $data[6];
                    $reference = $data[7];
                }
                if(!empty($datedelarret))
                {
                    $datedelarret = str_replace(":","/",$datedelarret);
                    $datedelarret = str_replace("-","/",$datedelarret);
                    list($jour,$mois,$annee) = explode("/",$datedelarret);
                    $datedelarret = $annee.'/'.$mois.'/'.$jour; // Permet de gérer le format de la date
                }
                
                $doublon = 'SELECT COUNT(*) AS doublon FROM test
                WHERE requerantes = :requerantes AND etatdefendeur = :etatdefendeur AND datedelarret = :datedelarret  AND articles = :articles 
                AND thematique = :thematique AND auteur = :auteur AND revue = :revue AND reference = :reference; ';
                $sth = $db->prepare($doublon);
                $sth->bindValue(':requerantes', $requerantes, PDO::PARAM_STR);
                $sth->bindValue(':etatdefendeur', $etatdefendeur, PDO::PARAM_STR);
                $sth->bindValue(':datedelarret', $datedelarret, PDO::PARAM_STR);
                $sth->bindValue(':articles', $articles, PDO::PARAM_STR);
                $sth->bindValue(':thematique', $thematique, PDO::PARAM_STR);
                $sth->bindValue(':auteur', $auteur, PDO::PARAM_STR);
                $sth->bindValue(':revue', $revue, PDO::PARAM_STR);
                $sth->bindValue(':reference', $reference, PDO::PARAM_STR);
                $sth->execute();

                $row = $sth->fetch(PDO::FETCH_ASSOC);
            
                $doublon_exist = $row['doublon']>0;
                
                if($doublon_exist)
                {
                    $item = array($ro,$requerantes, $etatdefendeur,$datedelarret,$articles,$thematique,$auteur,$revue,$reference);
                    array_push($doublon_arr, $item);
                    echo ('<br><strong>La ligne '.$ro.' est déjà enregistrée :</strong><br>
                                Requérant.e.s : '.$requerantes.' <br>
                                État défendeur : '.$etatdefendeur.' <br>
                                Date de l\'arret : '.$datedelarret.'<br>
                                Articles : '.$articles.' <br>
                                Thématiques : '.$thematique.' <br>
                                Auteur : '.$auteur.' <br>
                                Revue : '.$revue.' <br>
                                Références : '.$reference.' <br>'
                            );
                }
                else if(!empty($requerantes) || !empty($reference)  )
                {
                    $requerantes = addslashes($requerantes);
                    $etatdefendeur = addslashes($etatdefendeur);
                    $articles = addslashes($articles);
                    $thematique = addslashes($thematique);
                    $auteur = addslashes($auteur);
                    $revue = addslashes($revue);
                    $reference = addslashes($reference);
                    $ajout++;
                    $end = " VALUES ('".$requerantes."','".$etatdefendeur."','".$datedelarret."','".$articles."','".$thematique."','".$auteur."','".$revue."','".$reference."'); ";
                    $query = $begin.$end;
                    $stmt = $db->prepare($query);
                    $stmt->execute();
                }
            }
            fclose($handle);
            echo '<br><strong>Nombre d\'ajouts : '.$ajout.'</strong>';
        }
    }
}


function gestion(){
      
    ?>


<html>
<head>
	<meta charset="utf-8">
	<link rel="shortcut icon" type="image/ico" href="http://www.datatables.net/favicon.ico">
	<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1.0, user-scalable=no">
	<title>Gestion enregistrement</title>
	<link rel="stylesheet" type="text/css" href="../wp-content/plugins/MoteurCEDH/gestionEnregistrements/css/jquery.dataTables.min.css">
	<link rel="stylesheet" type="text/css" href="../wp-content/plugins/MoteurCEDH/gestionEnregistrements/css/buttons.dataTables.min.css">
	<link rel="stylesheet" type="text/css" href="../wp-content/plugins/MoteurCEDH/gestionEnregistrements/css/select.dataTables.min.css">
	<link rel="stylesheet" type="text/css" href="../wp-content/plugins/MoteurCEDH/gestionEnregistrements/css/editor.dataTables.min.css">
	<link rel="stylesheet" type="text/css" href="../wp-content/plugins/MoteurCEDH/gestionEnregistrements/resources/syntax/shCore.css">
	<link rel="stylesheet" type="text/css" href="../wp-content/plugins/MoteurCEDH/gestionEnregistrements/resources/demo.css">
	<style type="text/css" class="init">
    .containe{
        display: flex;
  justify-content: space-between;
    }
	div.containerPays {
        margin: 0;
    }
    div.containerAuteur {
    }
	</style>
    <script type="text/javascript" language="javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>
	<script type="text/javascript" language="javascript" src="../wp-content/plugins/MoteurCEDH/gestionEnregistrements/js/jquery-3.3.1.js"></script>
	<script type="text/javascript" language="javascript" src="../wp-content/plugins/MoteurCEDH/gestionEnregistrements/js/jquery.dataTables.min.js"></script>
	<script type="text/javascript" language="javascript" src="../wp-content/plugins/MoteurCEDH/gestionEnregistrements/js/dataTables.buttons.min.js"></script>
	<script type="text/javascript" language="javascript" src="../wp-content/plugins/MoteurCEDH/gestionEnregistrements/js/dataTables.select.min.js"></script>
	<script type="text/javascript" language="javascript" src="../wp-content/plugins/MoteurCEDH/gestionEnregistrements/js/dataTables.editor.min.js"></script>
    <script type="text/javascript" language="javascript" src="//cdn.datatables.net/plug-ins/1.10.19/sorting/datetime-moment.js"></script>
	<script type="text/javascript" language="javascript" src="../wp-content/plugins/MoteurCEDH/gestionEnregistrements/resources/syntax/shCore.js"></script>
	<script type="text/javascript" language="javascript" src="../wp-content/plugins/MoteurCEDH/gestionEnregistrements/resources/demo.js"></script>
	<script type="text/javascript" language="javascript" src="../wp-content/plugins/MoteurCEDH/gestionEnregistrements/resources/editor-demo.js"></script>
	<script type="text/javascript" language="javascript" class="init">
	


var editor; // use a global for the submit and return data rendering in the examples

$(document).ready(function() {



	editor = new $.fn.dataTable.Editor( {
		ajax: "../wp-content/plugins/MoteurCEDH/gestionEnregistrements/controllers/controller.php",
		table: "#example",
		fields: [ {
				label: "Référence:",
				name: "reference.libelle",
                type:'textarea'
			}, {
				label: "Date de l'arrêt:",
				name: "reference.datedelarret",
				type: "datetime"
			}, {
				label: "Revue:",
				name: "reference.revue",
                type:'textarea'
			}, {
				label: "auteur:",
				name: "reference.code_auteur",
                type:'select'
			},
            {
				label: "Pays:",
				name: "reference.code_pays",
                type:'select'
			}
		],
        i18n: {
            create: {
                button: "Nouveau",
                title:  "Créer nouvelle entrée",
                submit: "Créer"
            },
            edit: {
                button: "Modifier",
                title:  "Modifier entrée",
                submit: "Actualiser"
            },
            remove: {
                button: "Supprimer",
                title:  "Supprimer",
                submit: "Supprimer",
                confirm: {
                    _: "Etes-vous sûr de vouloir supprimer %d lignes?",
                    1: "Etes-vous sûr de vouloir supprimer 1 ligne?"
                }
            },
            error: {
                system: "Une erreur s’est produite, contacter l’administrateur système"
            },
            datetime: {
                previous: 'Précédent',
                next:     'Premier',
                months:   [ 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre' ],
                weekdays: [ 'Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam' ]
            }
        }
	} );


	$('#example').DataTable( {

		dom: "Blfrtip",
		ajax: "../wp-content/plugins/MoteurCEDH/gestionEnregistrements/controllers/controller.php",
		lengthMenu: [ 10, 20, 50, 100, 200, 500,1000],
        order: [[ 1, 'asc' ]],
		columns: [
			{
				data: null,
				defaultContent: '',
				className: 'select-checkbox',
				orderable: false
			},
			{ data: "reference.libelle" },
			{ data: "reference.datedelarret" },
			{ data: "reference.revue" },
            { data: "auteur.nom" },
			{ data: "pays.libelle"}
			
		],
        select: {
            style:    'os',
            selector: 'td:first-child'
        },
        
		buttons: [
			{ extend: "create", editor: editor },
			{ extend: "edit",   editor: editor },
			{ extend: "remove", editor: editor },

		],

        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.22/i18n/French.json",
            }
	} );
} );
$(document).ready(function() {



editor = new $.fn.dataTable.Editor( {
    ajax: "../wp-content/plugins/MoteurCEDH/gestionEnregistrements/controllers/controllerPays.php",
    table: "#pays",
    fields: [ 
        {
            label: "Nom du pays:",
            name: "libelle",
            type:'textarea'
        }
        
    ],
    i18n: {
        create: {
            button: "Nouveau",
            title:  "Créer nouvelle entrée",
            submit: "Créer"
        },
        edit: {
            button: "Modifier",
            title:  "Modifier entrée",
            submit: "Actualiser"
        },
        remove: {
            button: "Supprimer",
            title:  "Supprimer",
            submit: "Supprimer",
            confirm: {
                _: "Etes-vous sûr de vouloir supprimer %d lignes?",
                1: "Etes-vous sûr de vouloir supprimer 1 ligne?"
            }
        },
        error: {
            system: "Une erreur s’est produite, contacter l’administrateur système"
        },
        datetime: {
            previous: 'Précédent',
            next:     'Premier',
            months:   [ 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre' ],
            weekdays: [ 'Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam' ]
        }
    }
} );


$('#pays').DataTable( {

    dom: "Blfrtip",
    ajax: "../wp-content/plugins/MoteurCEDH/gestionEnregistrements/controllers/controllerPays.php",
    lengthMenu: [ 10, 20, 50, 100, 200, 500,1000],
    order: [[ 1, 'asc' ]],
    columns: [
        {
            data: null,
            defaultContent: '',
            className: 'select-checkbox',
            orderable: false
        },
        { data: "libelle" }
        
    ],
    select: {
        style:    'os',
        selector: 'td:first-child'
    },
    
    buttons: [
        { extend: "create", editor: editor },
        { extend: "edit",   editor: editor },
        { extend: "remove", editor: editor },

    ],

    "language": {
        "url": "//cdn.datatables.net/plug-ins/1.10.22/i18n/French.json",
        }
} );
} );

$(document).ready(function() {



editor = new $.fn.dataTable.Editor( {
    ajax: "../wp-content/plugins/MoteurCEDH/gestionEnregistrements/controllers/controllerAuteur.php",
    table: "#auteur",
    fields: [ 
        {
            label: "Nom de l'auteur:",
            name: "nom",
            type:'textarea'
        },
        {
            label: "Prénom de l'auteur:",
            name: "prenom",
            type:'textarea'
        }
        
    ],
    i18n: {
        create: {
            button: "Nouveau",
            title:  "Créer nouvelle entrée",
            submit: "Créer"
        },
        edit: {
            button: "Modifier",
            title:  "Modifier entrée",
            submit: "Actualiser"
        },
        remove: {
            button: "Supprimer",
            title:  "Supprimer",
            submit: "Supprimer",
            confirm: {
                _: "Etes-vous sûr de vouloir supprimer %d lignes?",
                1: "Etes-vous sûr de vouloir supprimer 1 ligne?"
            }
        },
        error: {
            system: "Une erreur s’est produite, contacter l’administrateur système"
        },
        datetime: {
            previous: 'Précédent',
            next:     'Premier',
            months:   [ 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre' ],
            weekdays: [ 'Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam' ]
        }
    }
} );


$('#auteur').DataTable( {

    dom: "Blfrtip",
    ajax: "../wp-content/plugins/MoteurCEDH/gestionEnregistrements/controllers/controllerAuteur.php",
    lengthMenu: [ 10, 20, 50, 100, 200, 500,1000],
    order: [[ 1, 'asc' ]],
    columns: [
        {
            data: null,
            defaultContent: '',
            className: 'select-checkbox',
            orderable: false
        },
        { data: "nom" },
        { data: "prenom" }
        
    ],
    select: {
        style:    'os',
        selector: 'td:first-child'
    },
    
    buttons: [
        { extend: "create", editor: editor },
        { extend: "edit",   editor: editor },
        { extend: "remove", editor: editor },

    ],

    "language": {
        "url": "//cdn.datatables.net/plug-ins/1.10.22/i18n/French.json",
        }
} );
} );

	</script>
</head>
<body class="dt-example php">
	<div class="cont">
		<section>
			<h1>Gestion des enregistrements</h1>
			<div class="demo-html">
				<table id="example" class="display" cellspacing="0" width="100%">
					<thead>
						<tr>
							<th></th>
							<th>Référence</th>
							<th>Date de l'arrêt</th>
							<th>Revue</th>
							<th>Auteur</th>
                            <th>Pays</th>
						</tr>
					</thead>
				</table>
			</div></br></br></br></br>
    <div class="containe">
        <div class="containerPays">
            <div class="demo-html">
				<table id="pays" class="display" cellspacing="0" width="100%">
					<thead>
						<tr>
                            <th></th>
							<th>Nom du pays</th>
						</tr>
					</thead>
				</table>
			</div>
        </div>
        <div class="containerAuteur">
            <div class="demo-html">
				<table id="auteur" class="display" cellspacing="0" width="100%">
					<thead>
						<tr>
                            <th></th>
							<th>Nom de l'auteur</th>
                            <th>Prénom de l'auteur</th>
						</tr>
					</thead>
				</table>
			</div>
        </div>
    </div>
		</section>
	</div>
</body>
</html>
<?php

}?>