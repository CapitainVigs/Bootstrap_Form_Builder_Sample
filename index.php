<?php
     $servername = "localhost";
        $username = "root";
        $password = "";

 try {
              $conn = new PDO("mysql:host=$servername;dbname=formsbuild", $username, $password);
              // set the PDO error mode to exception
              $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
        catch(PDOException $e)
            {
              echo "Connection failed: " . $e->getMessage();
            }
//renvoi la position du mot chercher

//Prend en parametre le text avec les limites afin d'extraire la valeur
function getTheWord($text, $firstPosition, $lastPosition)
{
 return substr($text, $firstPosition,($lastPosition-$firstPosition));
}




function variableName($data,$word)
{
 
 $temp_position=0;
 $temp_position_end=0;
 $variable= array();

while (true) {


 $temp_position=strpos($data,$word, $temp_position);
      if($temp_position===false)
      {
       break;
      }else 
      {
        $temp_position=strpos($data,'\\', $temp_position);
        $temp_position++; //on cherche le caractere '\' et on se place devant le caractere
       
      
        $temp_position=strpos($data,'\\', $temp_position);
         $temp_position+=2;
       
        $temp_position_end=strpos($data,'\\', $temp_position);// la taille de la variable,
        array_push($variable, str_replace('-','',getTheWord($data,$temp_position,$temp_position_end)));
      }

   
}
return $variable;
}



function variableNameAndOther($data,$word1,$word2)
{
 
 $temp_position=0;
 $temp_position_alpha=0;
 $temp_position_beta=0;
 $temp_position_end=0;
 $variable= array();
 $temp_word1;
 $temp_word2;

while (true) {


 $temp_position=strpos($data,$word1, $temp_position);
      if($temp_position===false)
      {
       break;
      }else 
      {
        $temp_position=strpos($data,'\\', $temp_position);
        $temp_position++; //on cherche le caractere '\' et on se place devant le caractere
       
      
        $temp_position=strpos($data,'\\', $temp_position);
         $temp_position+=2;
       
        $temp_position_end=strpos($data,'\\', $temp_position);// la taille de la variable,
        $temp_word1=str_replace(' ','_',getTheWord($data,$temp_position,$temp_position_end));
        $temp_word1=str_replace('(', '', $temp_word1);
        $temp_word1=str_replace(')', '', $temp_word1);

        $temp_position=strpos($data,$word2, $temp_position_end);
        $temp_position_begin=strpos($data,$word1, $temp_position_end);
        if($temp_position===false|| $temp_position_begin < $temp_position)
          {
            $temp_position=$temp_position_end;
           continue;
          }
          else
          {
            $temp_position=strpos($data,'\\', $temp_position);
            $temp_position++; //on cherche le caractere '\' et on se place devant le caractere
       
      
            $temp_position=strpos($data,'\\', $temp_position);
            $temp_position+=2;
       
            $temp_position_end=strpos($data,'\\', $temp_position);// la taille de la variable,


        $temp_word2=str_replace('-','',getTheWord($data,$temp_position,$temp_position_end));


        array_push($variable,$temp_word2."_AND_".$temp_word1 );
      }
      }

   
}
return $variable;
}



function tableCreate($data, $table,$brute)
{

$nameValue=variableNameAndOther($brute,'label','name');



    if(sizeof($nameValue)!=0)
    {
         $servername = "localhost";
        $username = "root";
        $password = "";
          try {
              $conn = new PDO("mysql:host=$servername;dbname=formsbuild", $username, $password);
              // set the PDO error mode to exception
              $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
          catch(PDOException $e)
            {
              echo "Connection failed: " . $e->getMessage();
            }

   
       
            $tableName="builder".rand();


         
        try {
            // sql to create table
            $sql = "CREATE TABLE $tableName (id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,";

            foreach ($nameValue as $value) {
              $sql =$sql."$value  VARCHAR(100),";
            }

            $sql = substr($sql, 0, -1);
            $sql =$sql." )";
            
            // use exec() because no results are returned
            
            $conn->exec($sql);
            

             $sql = "INSERT INTO tablestore (libTable, content, tableName) VALUES ('".$tableName."', '".$data."','".$table."')";
              $conn->exec($sql);


            }
        catch(PDOException $e)
            {
            echo $sql . "<br>" . $e->getMessage();
            }
    }
}

//une fonction qui permet la suppression des tables.
function deleteTable($libTable)
{


}

//listing des formulaires enregistrés

        try {
              $conn = new PDO("mysql:host=$servername;dbname=formsbuild", $username, $password);
              // set the PDO error mode to exception
              $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
        catch(PDOException $e)
            {
              echo "Connection failed: " . $e->getMessage();
            }
            $tableName="builder".rand();





try{

$affiche_les_formulaires=$conn->prepare("SELECT * FROM tablestore ");

//param IDDEMANDEUR on assigne la valeur id de la personne qui ouvre la session 

$affiche_les_formulaires->execute();
$affiche_les_formulaires->setFetchMode(PDO::FETCH_ASSOC);

while ($data = $affiche_les_formulaires->fetch()) {
        $les_formulaires[] = $data;
      }
        }

catch(PDOException $err)
{
die('Erreur : '.$err->getMessage());
} 



// pour enregistrer les modifications 
if(isset($_GET["Action"]) AND $_GET["Action"]=="rec")
    {

      tableCreate($_POST['content'],$_POST['idForme'],$_POST['dataBrute']);
      echo "Forme Enregistré !!";
     exit();

}

// pour modifier les formulaires existantes 
if(isset($_GET["Action"]) AND $_GET["Action"]=="mod")
    {

      $_POST['newNameTable']=trim($_POST['newNameTable']);

    
    foreach ($les_formulaires as $forme)  { 
    if($forme['libTable']==$_POST['oldTableLib'])
        if($forme['tableName']!=$_POST['newNameTable']){
            tableCreate($_POST['content'],$_POST['idForme'],$_POST['dataBrute']);
            echo "Forme new Enregistré !!";
            exit();
          }

        else{

            //une requete pour recuper l'ancien contenu brut du formulaire modifié
              $oldContentTable=$conn->prepare("SELECT * FROM tablestore WHERE libTable=:libTable ");
              $oldContentTable->bindParam(':libTable',$libTable);
              $libTable=$_POST['oldTableLib'];
              $oldContentTable->execute();
              $oldContentTable->setFetchMode(PDO::FETCH_ASSOC);

              $bruteContentOld=$oldContentTable->fetch();

              $oldTableVariable=variableNameAndOther(json_encode($bruteContentOld),'label','name');
              $newTableVariable=variableNameAndOther($_POST['dataBrute'],'label','name');

              $newVariablesCreated=array_diff($newTableVariable, $oldTableVariable);
              $oldVariableRemoved =array_diff($oldTableVariable, $newTableVariable);
              // si il n'y a pas eu de changement de form prenant des valeurs( des inputs)
              if($newVariablesCreated==null AND $oldVariableRemoved==null)
              {

                $sql = "UPDATE tablestore SET content=$_POST[dataBrute] WHERE libTable='".$_POST['oldTableLib']."'";
                  // Prepare statement
                  $stmt = $conn->prepare($sql);

                  // execute the query
                  $stmt->execute();
                  echo " Modification effectuée avec success";
                 
              }

              if($newVariablesCreated!=null AND $oldVariableRemoved==null)
              {



                $sql="ALTER TABLE $_POST[oldTableLib] ADD ";
                foreach ($newVariablesCreated as $colums) {
                 $sql=$sql.$colums." VARCHAR (100) ,";
                }
                $sql=   substr($sql, 0, -1);
           
           $sql2 = "UPDATE tablestore SET content=$_POST[dataBrute] WHERE libTable='".$_POST['oldTableLib']."'";

           $stmt1 = $conn->prepare($sql);
           $stmt2 = $conn->prepare($sql2);

            $stmt1->execute();
            $stmt2->execute();
            echo " Modification de column effectuée avec success";
              }




              exit();
            }
                                          
  }

      tableCreate($_POST['content'],$_POST['idForme'],$_POST['dataBrute']);
      echo "Forme Enregistré !!";
     exit();

}




    




    


?>
<!DOCTYPE html>
<html lang="en">
    <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../../favicon.ico">
  <link rel="stylesheet" type="text/css" href="assets/css/demo.css">
  <link rel="stylesheet" type="text/css" media="screen" href="assets/css/form-builder.min.css">
  <link rel="stylesheet" type="text/css" media="screen" href="assets/css/form-render.min.css">
 
    <title>Stater template</title>

    <!-- Bootstrap core CSS -->
    <link href="bootstrap.min.css" rel="stylesheet">
    <link href="custom-style.css" rel="stylesheet">

    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <link href="../../assets/css/ie10-viewport-bug-workaround.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="navbar-static-top.css" rel="stylesheet">

    <!-- Just for debugging purposes. Don't actually copy these 2 lines! -->
    <!--[if lt IE 9]><script src="../../assets/js/ie8-responsive-file-warning.js"></script><![endif]-->
    <script src="../../assets/js/ie-emulation-modes-warning.js"></script>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>

  <body>
  <div class="row">
  <form method="post">
        <div class="form-group ">
             <label  class="col-md-2 control-label">Choisir un formulaire  :</label>
                <div class="col-md-3">
                <select  name="idUtilisateur" class="form-control btn-primary" >
                <option value="" id="debut" selected>LISTE DES FORMULAIRES EXISTANTES</option>
                <?php if(isset($les_formulaires))
                foreach ($les_formulaires as $formulaire)  { ?>

               <option title="<?= $formulaire['libTable']; ?>" value='<?= $formulaire['content']; ?>' > <?= $formulaire['tableName']; ?></option>
               <?php }?>
          </select>
         </div>
          
          </div>
            
    </form>
    </div>
<br/>
<br/>
<div class="content" >
    <input type="hidden" name="oldTableLib">
    <div class="input-group">
      <h1 class="input-group-addon" id="title">Création de Formulaire</h1>
      <input type="text" class="form-control " placeholder="Le Nom de Votre Formulaire " name="" value="" id="formName">
    </div>
    <div class="build-wrap"></div>
    <div class="render-wrap"></div>
    <button id="edit-form">Edit Form</button>
    <button class="btn btn-success" id= "submit" onclick="envoyer()"> Envoyer</button>
  </div>
    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script>window.jQuery || document.write('<script src="../../assets/js/vendor/jquery.min.js"><\/script>')</script>
    <script src="bootstrap.min.js"></script>
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/form-builder.min.js"></script>
    <script src="assets/js/form-render.min.js"></script>
    <script src="assets/js/demo.js"></script>


    <script >
    function envoyer(identifiant='0') {
      var tableName=$("input[id=formName]").val();
      var oldTableLibVal=$("input[name=oldTableLib]").val();
      //on se rassure que le formulaire est nommé
      if(tableName!=''){
        //on verifi si il ne s'agit pas d'une modification
              if(oldTableLibVal==''){
         
                    $.post("vente_agent.php?Action=rec",
                    {
                      idForme:     tableName,
                      content:     JSON.parse(window.sessionStorage.getItem('formData')),
                      dataBrute:    window.sessionStorage.getItem('formData')
                    },
                    function(data,status){
                      if(status=='success'){
                        alert(data);
                       window.sessionStorage.setItem('formData',"");
                       window.sessionStorage.setItem('title',"");
                       window.sessionStorage.setItem('name',"");
                      }
                      });
                      }
                    

              if(oldTableLibVal!=''){
         
                    $.post("vente_agent.php?Action=mod",
                    {
                      idForme:      tableName,
                      content:      JSON.parse(window.sessionStorage.getItem('formData')),
                      dataBrute:    window.sessionStorage.getItem('formData'),
                      oldTableLib:  oldTableLibVal,
                      newNameTable: $("input[id=formName]").val()
                    },
                    function(data,status){
                      if(status=='success'){
                        alert(data);
                       window.sessionStorage.setItem('formData',"");
                       window.sessionStorage.setItem('title',"");
                       window.sessionStorage.setItem('name',"");
                      }
                      });
                    }

               location.reload();
              }
              else 
                  {
                  alert("Veuillez donner un nom au formulaire");
                  }
        
}


$(document).ready( function() {

if((window.sessionStorage.getItem('title')!=null)){
  $("div[class=content] h1[id=title]").html("Modification de formulaire");
  $("div[class=content] input[name=oldTableLib]").val(window.sessionStorage.getItem('title'));
  $("div[class=content] input[id=formName]").val(window.sessionStorage.getItem('name'));
  $("select[name=idUtilisateur] option[id=debut]").html(window.sessionStorage.getItem('name'));
}

$('select[name=idUtilisateur]').on('change', function() {
  var text=this.value;
  var newTitle=$(this).find("option:selected").attr("title");
  var newName=$(this).find("option:selected").html();
  // alert(newName); on enregistre dans le cache les variable
  window.sessionStorage.setItem('title',newTitle);
  window.sessionStorage.setItem('name',newName);
  var temp1=JSON.stringify(text);
 
  window.sessionStorage.setItem('formData',temp1);
  location.reload();
 
});


      });

  </script>

  </body>
</html>


