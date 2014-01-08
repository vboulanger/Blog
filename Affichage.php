<?php

include_once 'Billet.php';
include_once 'Categorie.php';
include_once 'Utilisateur.php';


class Affichage{



	function affichageGeneral($articles, $categorie/*$contenu_central, $menu_droite, $menu_gauche*/){
		$file = 'BlogAlex.php';
		$content = file_get_contents($file);
		$content = str_replace("mesarticles", "$articles", $content);
		$content = str_replace("mescategories", "$categorie", $content);
		$content = str_replace("auteurlol", self::infoUser(), $content);
		$content = str_replace("mapagination", self::afficherNbPages(), $content);
		echo $content;
	}


	static function infoUser(){
		$info = 'Utilisateur<br>';
		if (!empty($_SESSION['login'])){
			$info .= 'Bonjour <a href="membre.php">'.htmlentities($_SESSION['login']).'</a><br>';
			$info .= '<a href="deconnexion.php">Se déconnecter</a><br>';
			include_once 'Utilisateur.php';
			if(Utilisateur::estAdmin($_SESSION['login'])==true){
				$info .= 'tu es admin/<br>';
			}else{
				$info .= 'tu es pas admin<br>';
			}
		}else{
			$info .= '<a href="connexion.php">Se connecter</a>';
		}

		return $info;
	}


	static function afficherNbPages(){
		$page = $_GET;

		//SI GET est vide num est égal à 1 
		if(sizeof($page)!=0){
			
			foreach ($page as $key => $value) {
				if ($key=="page"){
					$num = $value;
					//var_dump($value);
					break;
					//=0 quand y'a des param mais pas les bons
				}else {
					$num = 0;
				}  
			}
		}
		else {
			$num = 1;
		}
		//TODO gérer quand num page entré manuellement
		if($num==0) $pagination = '';
		else{
			$pagination = 'Page numéro : << '.$num.' >>';
			$prec = "<<";
			$suiv = ">>";
			$nb_billets = Billet::getNbBillet();
			if($num>1)
				$pagination = str_replace($prec, '<a href="Blog.php?page=' . ($num-1) . '"><<</a>' ,$pagination);
	
			if($nb_billets > $num*5)
				$pagination = str_replace($suiv, '<a href="Blog.php?page=' . ($num+1) . '">>></a>' ,$pagination);
			}
		return $pagination;
		
	}

	



	/* Fonction qui retourne le code HTML pour un billet */
	static function afficherBillet($billet){
		$date = $billet->getAttr("date");
		$date = substr($date, 0, 11) . "à " . substr($date, 11);
		$code = "<div class=\"Article\">\n" .
				"<h1>" . $billet->getAttr("titre") . "</h1>\n" .
				//"<h1>" . $billet->getAttr("titre") . "</h1><br>\n" . 
				"<p>" . $billet->getAttr("body") . "</p>\n" . 
				"<p id = \"date\"><i>" . "publié le " . $date. "</i> par ". $billet->getAttr("auteur")."</p>\n" .
				"</div>";
				
		return $code;
	}

	/* Fonction qui retourne le code HTML pour plusieurs billets
	 * sous la forme d'un tableau */
	static function afficheListeBillets($liste){
		$code = "";
		//var_dump($liste);
		if(sizeof($liste)==0){
			$code = "<div class=\"Article\">\n";
			$code = $code . "Aucun billet";
			$code = $code . "</div>";
			//TODO ajouter pagination
			return $code;
		}else if(sizeof($liste)==1){
			foreach ($liste as $billet) {
				$id = $billet->getAttr("id");
				$link = '<a href="Blog.php?a=detail&amp;id=' . $id . '">(suite)</a>';
				$date = $billet->getAttr("date");
				$date = substr($date, 0, 11) . "à " . substr($date, 11);

				
				$code = $code . "<div class=\"Article\" >\n" .
						"<h1>" . $billet->getAttr("titre") . "</h1><br>\n" .
						"<p>" . substr($billet->getAttr("body"),0,220) . "..." . $link . "</p>\n" .
						"<p id=\"date\"><i>" . "publié le " . $date. "</i></p>\n" .
						"</div>\n";
				
				//$code = $code . "</div>\n";
			}
			

		}else{
			//$code = $code . "<table>\n";
			foreach($liste as $billet){
				$id = $billet->getAttr("id");
				$link = '<a id_lien=' .$id . ' href="Blog.php?a=detail&amp;id=' . $id . '">(suite)</a>';
				$date = $billet->getAttr("date");
				$date = substr($date, 0, 11) . "à " . substr($date, 11);
				//$code = $code . "<tr><td>\n";

				

				$code = $code . "<div class=\"Article\">\n" .
						"<h1>" . $billet->getAttr("titre") . "</h1><br>\n" .
						"<p>" . substr($billet->getAttr("body"),0,220) . "..." . $link . "</p>\n" .
						"<p id=\"date\"><i>" . "publié le " . $date. "</i></p>\n" .
						"</div>\n";
				
						
				//$code = $code . self::afficherBillet($billet);
				//$code = $code . "</td></tr>\n";
			}
			
		//$code = $code . "</table>\n";
		}

		
		return $code;
	}


	/* Retourne le code HTML pour afficher tous les catégories
	 * présentent dans la BDD*/
	static function afficheListeCategorie($liste){
		$code = "";
		if(sizeof($liste)==0)
			$code = "Aucune catégorie";
		else{
			$code = 'Liste des catégories<br>';
			foreach ($liste as $categorie) {
				$titre = $categorie->getAttr("titre");
				$span = $categorie->getAttr("description");
				$code = $code . $titre . "<br>";
				//TODO Ajouter span et petit icone, en faire une liste
				//$code = $code . "<span TITLE =\"" . $span . ">" . $titre . "</span> <br>\n\t";
			}
		}
		return $code;
	}



	/* FAUTE DE FAIRE UNE AUTRE CLASSE CAR TROP RÉPÉTITIVE
	 * SERONT CI-DESSOUS LES METHODES UTILISÉES POUR L'AFFICHAGE
	 * DE LA PARTIE ADMINISTRATEUR */


	/* Permet d'ajouter un article */
	static function ajouterBillet(){
		/* Je en sais pas si ça marche mais on va essayer */
		if (isset($_POST['envoyer']) && $_POST['envoyer'] == 'Envoyer'){
			if (isset($_POST['ajouterTitre']) && !empty($_POST['ajouterTitre'])){
				if (isset($_POST['ajouterArticle']) && !empty($_POST['ajouterArticle'])){
					$titre = $_POST['ajouterTitre'];
					$body = $_POST['ajouterArticle'];
					$categ = $_POST['categ'];
					$log = $titre . " " . $body . " " . $categ;

					$cat = Categorie::findByTitre($categ);
					$b = new Billet();
					$b->setAttr("titre", $titre);
					$b->setAttr("body", $body);
					//$TODO faire attention aux accents
					$b->setAttr("cat_id", $cat->getAttr("id"));
					$b->setAttr("date", date("Y-m-d H:i:s"));
					$b->setAttr("auteur", $_SESSION['login']);

					$res =$b->insert();

					if($res==1) $log = 'Billet bien publié';
					else $log ='Une erreur';

					
				}else{
					$log = "manque l'article";
				}				
			}else{
				$log = "Manque un titre";
			}
		}
		/* Fin du test */



		$code = "<div class=\"Article\">\n" ;
		if(Utilisateur::estAdmin($_SESSION['login'])==false){
			//tu peux pas test tarba
			// <label for=\"ajouterTitre\">Titre de l'article</label><br />
			//<input id=\"ajouterTitre\" type=\"text\" name=\"titre\" autofocus>
       			
       			
			$code .= "Tarba t'as rien à faire ici<br>";
		}else{
			$categorie = Categorie::findAll();
			//wesh fais comme chez toi
			$code .= "<h1>Écrire un nouvel article</h1>";
			$code .= "

			<form method=\"post\" action=\"admin.php?a=addM\">
   			
   			<p> <label for=\"ajouterTitre\">Titre de l'article</label><br>
       			<textarea name=\"ajouterTitre\" id=\"ajouterTitre\">";
       			if (isset($_POST['ajouterTitre'])) $code .= $_POST['ajouterTitre'];
       			$code .= "</textarea>
       		</p>


   			<p>
       			<label for=\"ajouterArticle\">Zone d'écriture de l'article</label><br />
       			<textarea name=\"ajouterArticle\" id=\"ajouterArticle\">";
       			if (isset($_POST['ajouterArticle'])) $code .= $_POST['ajouterArticle'];
       			$code .= "</textarea>
   			";

   			$code .= '<input type="submit" name="envoyer" value="Envoyer" />
   			</p>';
   			
   			$code .=  self::listeCategorie();

   			$code .= '</form>';
   			//TODO créer ajoutmessage.php pour insérer dans la table


		
		}

		if (isset($log)){ $code .= $log; }
		
		$code .= "</div>";

		

		return $code;
	}


	/* Créé l'html pour afficher les catégories sous forme
	 * d'une liste */
	static function listeCategorie(){
		$liste = Categorie::findAll();
		$code = "";
		if(sizeof($liste)==0)
			$code = "Aucune catégorie";
		else{
			$code = "<p>
       					<label for=\"pays\">Dans quelle catégorie placez-vous le billet ?</label><br />
       					<select name=\"categ\" id=\"categ\">";
       		foreach ($liste as $categorie) {
       			$code .= "<option value=". $categorie->getAttr("titre") .">" .$categorie->getAttr("titre"). "</option>";
			}
			$code .= "</select></p>";
		}
		return $code;
	}

}

?>