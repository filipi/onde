<?php
    /**
     * 
     */
    class userInfo{
        private $user;
        private $validUser;
        private $baseQuery = "SELECT login,senha, email,\n
                                    nome, sobrenome,\n
                                    first, \"last_login\",\n
                                    endereco, cep, cidade,\n
                                    celular, avatar\n
                                FROM usuarios WHERE ";

        public function __construct($loginInfo, $type=0, $passwd=''){
            $passwd = addslashes(trim($passwd));
            $type = intval(trim($type));
            $loginInfo = addslashes(trim( $loginInfo));
            $query = $this->baseQuery . ($type ? "email= '" : "login= '") . $loginInfo . "'";
            if($passwd){ 
                $query .= " AND senha = '" . $passwd . "'";
            }
            //$query .= "AND ativo = true;";
            $result = pg_exec($query);
            if($result){
                $this->user = (pg_fetch_all($result))[0];
                $this->validUser = true;
                echo "<pre>";
                //var_dump($this->user);
                echo "</pre>";
            } else {
                $this->validUser = false;
                $this->user = null;
            }
        }

        public function getUserInfo($info){
            return $this->user[$info];
        }

        public function checkEmail($string){
            if($string == $this->getUserInfo('email'))
                return true;
            return false;
        }

        public function checkPasswd($string){
            if($string == $this->getUserInfo('passwd'))
                return true;
            return false;
        }

        public function isValidUser(){
            return $this->validUser;
        }


        




    }

?>