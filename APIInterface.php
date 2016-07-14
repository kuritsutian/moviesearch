<?php
include "MovieAPI.php";

if(!is_null($_REQUEST["type"])){
	if($_REQUEST["type"]=="list") { //Loading the popular actors list
		
		$movieDB = new MovieAPI();
		$actors = $movieDB->searchActors();
		if(!$movieDB->isError()){
			echo '<select id="popular_list" name="popular" onChange="fillName()">';
			foreach ($actors as $id => $actor){ //Adds an option for each actor aving the value as his/her id
				echo '<option value="'.$id.'">'.$actor.'</option>';
			}
			echo '</select>';
		} else echo $movieDB->getError();
		
	} elseif ($_REQUEST["type"]=="movies") { // Requests and displays the movies information
		
		$movieDB = new MovieAPI();
		$actor = $_REQUEST["actor"];
		
		if($actor == 0){//if no actor is selected on the popular list retrieves his/her id
			$actor = $movieDB->getActorIdByName($_REQUEST["name"]);
		}
		if($actor > 0){//if the actor was found displays the movie search results
			
			echo "<h3>Results:</h3>";
			$movies = $movieDB->searchMovies($actor);
			
			if(!$movieDB->isError()){
				foreach ($movies as $movie){
					echo "[External link] <a href='https://www.themoviedb.org/movie/".$movie['id']."' target='_blank'>".$movie['name']."</a>(".$movie['date'].")<br/>";
				}
			} else echo $movieDB->getError();
			
		} else echo "Actor not found.";
		
	} else {
		echo "Invalid request";
	}
}

?>