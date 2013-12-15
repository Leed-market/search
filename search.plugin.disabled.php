<?php
/*
@name search
@author Cobalt74 <cobalt74@gmail.com>
@link http://www.cobestran.com
@licence CC by nc sa http://creativecommons.org/licenses/by-nc-sa/2.0/fr/
@version 2.1.0
@description Le plugin search permet d'effectuer une recherche sur les articles de Leed. Ne perdez plus aucune information !
*/


// affichage d'un lien dans le menu "Gestion"
function search_plugin_AddLink_and_Search(){
	echo '<li><a class="toggle" href="#search">Rechercher articles</a></li>';
}

// affichage d'un formulaire de recherche dans la barre de menu
function search_plugin_menuForm(){
	echo '  <aside class="searchMenu">
			    <form action="settings.php#search" method="post">
					<input type="text" name="plugin_search" id="plugin_search" placeholder="..." value="'.(isset($_POST['plugin_search'])?$_POST['plugin_search']:"").'">
					<button type="submit">Rechercher</button>
				</form>';
	echo '  </aside>';
}

// affichage des option de recherche et du formulaire
function search_plugin_AddForm(){
	echo '<section id="search" name="search" class="search">
			<h2>Rechercher des articles</h2>
			<form action="settings.php#search" method="post">
				<input type="text" name="plugin_search" id="plugin_search" placeholder="..." value="'.(isset($_POST['plugin_search'])?$_POST['plugin_search']:"").'">
				<span>(3 car. min.)</span>
				<fieldset>
					<legend>Option de recherche</legend>';
	if (!isset($_POST['search_option']) ? $search_option=0 : $search_option=$_POST['search_option']);
	if($search_option==0) {
		echo '      <input type="radio" checked="checked" value="0" id="search_option_title" name="search_option"><label for="search_option_title">Titre</label>
					<input type="radio" value="1" id="search_option_content" name="search_option"><label for="search_option_content">+ Contenu</label>';
	} else {	
		echo '		<input type="radio" value="0" id="search_option_title" name="search_option"><label for="search_option_title">Titre</label>
					<input type="radio" checked="checked" value="1" id="search_option_content" name="search_option"><label for="search_option_content">+ Contenu</label>';
	}
	echo '      </fieldset>
				<fieldset>
					<legend>Affichage du résultat</legend>';
	if (!isset($_POST['search_show']) ? $search_show=0 : $search_show=$_POST['search_show']);
	if($search_show==0) {
		echo '      <input type="radio" checked="checked" value="0" id="search_show_title" name="search_show"><label for="search_show_title">Titre</label>
					<input type="radio" value="1" id="search_show_content" name="search_show"><label for="search_show_content">+ Contenu</label>';
	} else {	
		echo '		<input type="radio" value="0" id="search_show_title" name="search_show"><label for="search_show_title">Titre</label>
					<input type="radio" checked="checked" value="1" id="search_show_content" name="search_show"><label for="search_show_content">+ Contenu</label>';
	}
	echo '			</fieldset>
				<button type="submit">Rechercher</button>
			</form>';
    if(isset($_POST['plugin_search'])){
        if(strlen($_POST['plugin_search'])>=3){
			search_plugin_recherche();
		}else{ echo 'Saisir au moins 3 caractères pour lancer la recherche'; }
	}
	echo '</section>';
}


// foction de recherche des articles avec affichage du résultat.
function search_plugin_recherche(){
	$requete = 'SELECT id,title,guid,content,description,link,pubdate,unread, favorite
                FROM '.MYSQL_PREFIX.'event 
                WHERE title like \'%'.$_POST['plugin_search'].'%\'';
	if (isset($_POST['search_option']) && $_POST['search_option']=="1"){
		$requete = $requete.' OR content like \'%'.$_POST['plugin_search'].'%\'';
	}
	$requete = $requete.' ORDER BY pubdate desc';
    $query = mysql_query($requete);
	if($query!=null){
		echo '<div id="result_search" class="result_search">';
		while($data = mysql_fetch_array($query)){
			echo '<div class=search_article>
			        <div class="search_article_title">
			          <div class="search_buttonbBar">
				      	<span ';
				      	if(!$data['unread']){
				      		echo 'class="pointer right readUnreadButton eventRead"';
				      	}
				      	else {
				      		echo 'class="pointer right readUnreadButton"';
				      	}
				      	echo ' onclick="search_readUnread(this,'.$data['id'].');">marquer '.(!$data['unread']?'non lu':'lu').'</span>
				      	<span ';
				      	if($data['favorite']){
				      		echo 'class="pointer right readUnreadButton eventFavorite"';
				      	}
				      	else {
				      		echo 'class="pointer right readUnreadButton"';
				      	}
				      	echo ' onclick="search_favorize(this,'.$data['id'].');">'.(!$data['favorite']?'Favoriser':'Défavoriser').'</span>';
			echo '	</div>'.
				date('d/m/Y à H:i',$data['pubdate']).
				' - <a title="'.$data['guid'].'" href="'.$data['link'].'" target="_blank">
					     '.$data['title'].'</a>
					</div>';
			if (isset($_POST['search_show']) && $_POST['search_show']=="1"){
				echo '<div class="search_article_content">
					     '.$data['content'].'
				     </div>';
			}
			echo '</div>';
		}
		echo '</div>';
	}
}
Plugin::addJs("/js/search.js");

// Ajout de la fonction au Hook situé avant l'affichage des évenements
Plugin::addHook("setting_post_link", "search_plugin_AddLink_and_Search");
Plugin::addHook("setting_post_section", "search_plugin_AddForm");
//Ajout de la fonction au Hook situé après le menu des fluxs
Plugin::addHook("menu_post_folder_menu", "search_plugin_menuForm");
?>
