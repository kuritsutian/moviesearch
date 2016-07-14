<?php
/**
Class responsible for the comunication with "The movie DB" API,
providing the requested formated information. 

History:
13/07/2016 - Cristian Solano:  Creation
*/
class MovieAPI{
            
            private $_url;
            private $_token;
			private $_criteria;
            
            private $_error_message;
            private $_isError;
			
			/**
			Constructor, Initializes conection variables
			*/
            function __construct() {
                    $this->_token = 'e151b74ea57d7a6f2b335282f148678c';
                    $this->_url = 'https://api.themoviedb.org/3';
					$this->_isError = false;
            }
			/**
			Responsible for procesing the movie information string and appending it
			into an incoming array with the date, id and name of the movie.
			
			Input:
				- String $string: json movie information of an artist.
				- Array $results: temporary procesed results 
					$results[]['date']: date of the movie
					$results[]['id']: ID of the movie
					$results[]['name']: name of the movie
			Output:
				- Array $results: temporary procesed results 
					$results[]['date']: date of the movie
					$results[]['id']: ID of the movie
					$results[]['name']: name of the movie
			*/
			private function parseMovies($string, $results){
				$response = json_decode($string, true);
				foreach($response['results'] as $result){
					$index = sizeof($results);
					$results[$index]['name'] = $result['original_title'];
					$results[$index]['id'] = $result['id'];
					$results[$index]['date'] = $result['release_date'];
				}
				return $results;
			}
			
			/**
			Responsible for procesing the popular actors names string and
			appending it into an incoming array with other Actors information.
			
			Input:
				- String $string: json actors information.
				- Array $results: temporary procesed results 
					$results[<actor`s id>]: Name of the artist
			Output:
				- Array $results: temporary procesed results 
					$results[<actor`s id>]: Name of the artist
			*/			
			private function parseNames($string,$results){
				$response = json_decode($string, true);
				foreach($response['results'] as $result){
					$index = sizeof($results);
					$results[$result['id']] = $result['name'];
				}
				return $results;
			}
			
			/**
			Extracts the Id of an actor given the raw artist information.
			
			Input:
				- String $string: json actors information.
			Output:
				- String : actor`s id.
			*/
			private function extractId($string){
				$result = json_decode($string, true);
				if(sizeof($result['results'])>0)
					return $result['results'][0]['id'];
				else return 0;
			}
			
			/**
			Sets the error message
			
			Input:
				- String $error: Error Message.
			*/
			Private function setError($error){
				$this->_isError = true;
				$this->_error_message = $error;
			}
			
			/**
			Realizes the https request with "The movie DB" and returns
			the API result.
			
			Output:
				- String $data: Raw response from API.			
			*/
			private function APIRequest(){
				
				$url = $this->_url.$this->_criteria;
                
				try{
					$ch = curl_init();
					
					curl_setopt($ch, CURLOPT_URL, $url);
					curl_setopt($ch, CURLOPT_HEADER,0); //Change this to a 1 to return headers
					curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER["HTTP_USER_AGENT"]);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					
					$data = curl_exec($ch);
					
					if(curl_errno($ch) != 0) {
						$this->setError("Service temporary unavailable");
						curl_close($ch);
						return false;
					}
					curl_close($ch);
				} catch ( Exception $e){
					$this->setError("Conectivity error");
				}
				
                return $data;
			}
			
			/**
			Returns all the movies by an actor ID ordered by release date
			
			Input:
				- String $actor: Actor Id.
			Output:
				- Array $result[n]: Array with all the Actor's movies 
					$result[]['date']: date of the movie
					$result[]['id']: ID of the movie
					$result[]['name']: name of the movie
			*/
			function searchMovies($actor){
				$result = array();
				$page = 1;
				do{
					$previous_size = sizeof($result);
					$this->_criteria = "/discover/movie?api_key=".$this->_token."&with_people=".$actor."&sort_by=release_date.asc&page=".$page;
					$movies = $this->APIRequest();
					$result = $this->parseMovies($movies, $result);
					$new_size = sizeof($result);
					$page++;
				} while ($previous_size != $new_size); //While there is still results
				return $result;
			}
			
			/**
			Returns a list of popular actors
			
			Output:
				- Array $result[n]: List of popular acctors
					$result[<actor`s id>]: Name of the artist
			*/
			function searchActors(){
				
				$result = array(0 => "Suggested Popular Artists ...");
				
				for($i=1;$i<=10;$i++){ //Top 10 pages of popular actors
					$this->_criteria = "/person/popular?api_key=e151b74ea57d7a6f2b335282f148678c&page=".$i;
					$actors = $this->APIRequest();
					$result = $this->parseNames($actors,$result);
				}
				return $result;
			}
			
			/**
			Returns the Actor's id given his/her name
			
			Input:
				- String $actor: Actor's name
			Output:
				- String $actor: actor`s id.
			*/
			function getActorIdByName($actor){
				$this->_criteria = "/search/person?api_key=".$this->_token.'&query='.urlencode($actor).'';
				$actor = $this->extractId($this->APIRequest());
				return $actor;
			}
			
			/**
			Determines if an error occured while processing the request
			
			Output:
				- boolean : TRUE if an error occured, FALSE if no error occured.
			*/
			function isError(){
				return $this->_isError;
			}
			
			/**
			Returns the error message
			
			Output:
				- String : Text of the error.
			*/
			function getError(){
				return $this->_error_message;
			}
}
?>