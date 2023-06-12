<?php
   
  $data = file_get_contents("data.json");
  $obj = json_decode($data);
?>



<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Document</title>
   <link rel="stylesheet" href="sample-style-to-pdf.css">
</head>
<body> 
      
         <p><?php echo $obj->nom_region; ?></p>
         <p><?php echo $obj->population; ?></p>
         <p><?php echo $obj->superficie; ?></p>
         <p><?php echo $obj->nb_departements; ?></p>
         <p><?php echo $obj->logo_region; ?></p>
      
      <section>
         
         
      </br>

         <h1>Résultats trimestriels XX-YYYY</h1>

      </br>

         <p>Texte d'introduction par le client</p>

      </br>

         <table>
            <tr>
               <th scope="col">Nom du produit</th>
               <th scope="col">Ventes du trimestre</th>
               <th scope="col">Chiffre d'affaire du trimestre</th>
               <th scope="col">Ventes du même trimestre année précédente</th>
               <th scope="col">CA du même trimestre année précédente</th>
               <th scope="col">Evolution de CA en %age et en valeur absolue</th>

            </tr>
            <tr>
               <td></td>
               <td></td>
               <td></td>
               <td></td>
               <td></td>
               <td></td>
               <td></td>
            </tr>

         </table>

      </section>
   </div>
      
      
      <section>
         <h1>Avatars</h1>
         <figure>
            <img src="" alt="photo">
            <figcaption>

            </figcaption>
         </figure>
         <figure>
            <img src="" alt="photo">
            <figcaption>

            </figcaption>
         </figure>
         <figure>
            <img src="" alt="photo">
            <figcaption>

            </figcaption>
         </figure>
         
      </section>
   
 
   </div>

      <section>
      </br>
         <a href="https://www.orange.fr/portail" >Lien vers la page web fictive</a>
      </br>
         <figure>
            <img src="" alt="Qr code de l'image">
            <figcaption>

            </figcaption>
         </figure>
      </br>
         
      </section>
   
 
   </div>

      <section>
         <div class="bottom">JJ-MM-AAAA HH:MM</div>
      </section>
   
</body>
   
</html>