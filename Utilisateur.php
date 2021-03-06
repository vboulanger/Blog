<?php

include_once 'Base.php';

/* Sur la même base que Billet ou Catégorie
 * Ne sera donc pas détaillé
 */

class Utilisateur{

	private $userid;
	private $login;
	private $password;///haché, salé, rehaché (sha1)
	private $mail;
	private $chmod; //1 si admin, 0 sinon


	public function __toString(){
		return "[".__CLASS__ . "] <br>
			userid : ". $this->userid . "<br>
			login : ". $this->getAttr("login") ."<br>
			password : ". $this->getAttr("password") ."<br>
			mail : ". $this->getAttr("mail") . "<br>
			admin : ". $this->getAttr("chmod");
	}


	



	public function getAttr($attr_name){
		if(property_exists(__CLASS__, $attr_name)){
			return $this->$attr_name;
		}
		$emess = __CLASS__ . ": unknown member $attr_name (getAttr)";
		throw new Exception($emess, 45);
	}



	public function setAttr($attr_name, $attr_val){
		if(property_exists(__CLASS__, $attr_name)){
			$this->$attr_name=$attr_val;
			return $this->$attr_name;
		}
		$emess = __CLASS__ . ": unknown member $attr_name (setAttr)";
		throw new Exception($emess, 45);
	}




	public function update(){
		if(!isset($this->userid)){
			throw new Exception(__CLASS__ . ":Clé primaire non définie, update impossible");
		}

		$c = Base::getConnection();
		$salt = $this->getAttr("login");
		$this->password = sha1(sha1($this->password).$salt);
		$query = $c->prepare("update utilisateurs set login= ?, 
					password= ?, mail= ?, chmod= ?
					where userid= ?");

		$query->bindParam (1, $this->login, PDO::PARAM_STR);
		$query->bindParam (2, $this->password, PDO::PARAM_STR);
		$query->bindParam (3, $this->mail, PDO::PARAM_STR);
		$query->bindParam (4, $this->chmod, PDO::PARAM_STR);
		$query->bindParam (5, $this->userid, PDO::PARAM_STR);


		return $query->execute();

	}





	public function delete(){
		$nb = 0;
		if(isset($this->userid)){
			$query = "DELETE FROM Utilisateurs Where userid =" . $this->userid;
			$c = Base::getConnection();
			$nb = $c->exec($query);
		}
		return $nb;
	}



	public function insert(){
		$nb = 0;
		$salt = $this->login;
		$query = "INSERT INTO Utilisateurs VALUES(null, '".$this->login."','".sha1(sha1($this->password).$salt)."', '".$this->mail."', '".$this->chmod."')";
		$c = Base::getConnection();
		$nb = $c->exec($query);
		$this->setAttr("chmod", "0");
		$this->setAttr("userid", $c->LastInsertId());

		return $nb;
	}


	public static function findAll(){
		$query = "select * from utilisateurs";
		$c = Base::getConnection();
		$dbres = $c->prepare($query);
		$dbres->execute();
		$d = $dbres->fetchAll();
		$tab = Array();
		foreach ($d as $ligne) {
			$user = new Utilisateur();
			$user->setAttr("userid", $ligne["userid"]);
			$user->setAttr("login", $ligne["login"]);
			$user->setAttr("password", $ligne["password"]);
			$user->setAttr("mail", $ligne["mail"]);
			$user->setAttr("chmod", $ligne["chmod"]);
			array_push($tab, $user);
		}

		return $tab;
	}


	public static function findById($userid){
		$c = Base::getConnection();
		$query = 'select * from utilisateurs where userid= :userid';
		$dbres = $c->prepare($query);
		$dbres->bindParam(':userid', $userid);
		$dbres->execute();
		//$user = false;
		$d = $dbres->fetch(PDO::FETCH_OBJ);
		if($d == false) return false;
		$user = new Utilisateur();
		$user->setAttr("userid", $userid);
		$user->setAttr("login", $d->login);
		$user->setAttr("password", $d->password);
		$user->setAttr("mail", $d->mail);
		$user->setAttr("chmod", $d->chmod);

		return $user;
	}


	public static function findByLogin($login) {
		$c = Base::getConnection();
		$query = 'select * from utilisateurs where login= :login';
		$dbres = $c->prepare($query);
		$dbres->bindParam(':login', $login);
		$dbres->execute();
		//$user = false;
		$d = $dbres->fetch(PDO::FETCH_OBJ);
		if($d == false) return false;
		$user = new Utilisateur();
		$user->setAttr("login", $login);
		$user->setAttr("userid", $d->userid);
		$user->setAttr("password", $d->password);
		$user->setAttr("mail", $d->mail);
		$user->setAttr("chmod", $d->chmod);

		return $user;

	}


	public static function findByMail($mail) {
		$c = Base::getConnection();
		$query = 'select * from utilisateurs where mail= :mail';
		$dbres = $c->prepare($query);
		$dbres->bindParam(':mail', $mail);
		$dbres->execute();
		$user = false;
		$d = $dbres->fetch(PDO::FETCH_OBJ);
		if($d != false){
			$user = new Utilisateur();
			$user->setAttr("login", $d->login);
			$user->setAttr("userid", $d->userid);
			$user->setAttr("password", $d->password);
			$user->setAttr("mail", $d->mail);
			$user->setAttr("chmod", $d->chmod);
		}
		return $user;

	}


	public static function getNbUser(){
		$query = "select count(*) as nb from utilisateurs";
		$c = Base::getConnection();
		$res = $c->query($query);
		$data = $res->fetch();
		$nb = $data['nb'];

		return $nb;
	}


	public static function estAdmin($login){
		$current = self::findByLogin($login);
		$admin = $current->getAttr("chmod");
		if($admin==1) $res = true;
		else $res = false;

		return $res;
	}


}

?>