
# API de Gestion de Contenus, Commentaires et Utilisateurs

Développé par **Becue Melvin (Groupe E)**

---

## **Description**

Cette API a été conçue pour gérer un système de contenus, d'utilisateurs, de commentaires, et d'uploads. Elle utilise **Symfony** et **ApiPlatform** pour fournir des fonctionnalités avancées et sécurisées, avec une authentification basée sur des tokens JWT.

---

## **Fonctionnalités**

### **1. Gestion des contenus (`Content`)**
- Création, lecture, mise à jour et suppression de contenus.
- Association d'images (upload) aux contenus.
- Importation massive de contenus via un fichier CSV.

### **2. Gestion des commentaires (`Comment`)**
- Les utilisateurs peuvent commenter des contenus.
- Vérification des relations entre les utilisateurs, commentaires, et contenus.

### **3. Gestion des utilisateurs (`User`)**
- Création d'utilisateurs via l'API ou la ligne de commande.
- Attribution de rôles (`ROLE_USER`, `ROLE_ADMIN`).
- Authentification sécurisée via JWT.

### **4. Upload de fichiers (`Upload`)**
- Les fichiers peuvent être uploadés et associés à des entités (contenus, etc.).

---

## **Documentation d'utilisation**

### **Pré-requis**
- PHP 8.2 ou supérieur
- Composer
- Symfony CLI (optionnel)
- MySQL ou toute base de données compatible Doctrine

### **Installation**
1. Clonez le projet :
   ```bash
   git clone <url_du_repo>
   cd <nom_du_repo>
   ```

2. Installez les dépendances :
   ```bash
   composer install
   ```

3. Configurez votre base de données dans le fichier `.env` :
   ```
   DATABASE_URL="mysql://<user>:<password>@127.0.0.1:3306/<database_name>"
   ```

4. Appliquez les migrations :
   ```bash
   php bin/console doctrine:migrations:migrate
   ```

5. Démarrez le serveur local :
   ```bash
   symfony server:start
   ```

---

### **Exemple de configuration Postman**

#### **Obtenir un token JWT**
1. Créez un utilisateur via l'API ou la ligne de commande.
2. Faites une requête POST à `/api/login_check` avec le corps suivant :
   ```json
   {
       "username": "email@example.com",
       "password": "motdepasse"
   }
   ```
   **Réponse attendue :**
   ```json
   {
       "token": "votre_token_jwt"
   }
   ```

#### **Utiliser le token dans Postman**
- Ajoutez un en-tête `Authorization` :
  ```
  Authorization: Bearer votre_token_jwt
  ```

---

## **Gestion des rôles et droits**

### **Rôles disponibles**
- **`ROLE_USER`** : Rôle par défaut pour tous les utilisateurs.
- **`ROLE_ADMIN`** : Rôle administratif avec des permissions avancées.

### **Permissions par rôle**
| **Action**             | **ROLE_USER**  | **ROLE_ADMIN**  |
|-------------------------|----------------|-----------------|
| Lire un contenu         | ✅             | ✅              |
| Créer un contenu        | ❌             | ✅              |
| Modifier un contenu     | ❌             | ✅              |
| Supprimer un contenu    | ❌             | ✅              |
| Ajouter un commentaire  | ✅             | ✅              |
| Supprimer un commentaire| ❌             | ✅              |

---

## **Création d'un utilisateur en ligne de commande**

1. Utilisez la commande suivante :
   ```bash
   php bin/console app:create-user
   ```

2. Fournissez les informations demandées (email, mot de passe, rôle).

---

## **Importation de contenus via CSV**

### **Structure du fichier CSV**
Le fichier doit contenir les colonnes suivantes :
| **Colonne**       | **Description**                   |
|--------------------|-----------------------------------|
| `title`           | Titre du contenu                 |
| `meta_title`      | Titre pour le SEO                |
| `meta_description`| Description pour le SEO          |
| `content`         | Contenu principal                |
| `tags`            | Tags séparés par des virgules    |
| `cover`           | URL de l'image associée          |

### **Envoi via Postman**
- Endpoint : `/contents/import`
- Méthode : POST
- Body : Type `form-data`
  - Clé : `file`
  - Valeur : Fichier CSV

---

## **Tests et qualité**
- **PHPStan** :
  Analysez le code avec :
  ```bash
  vendor/bin/phpstan analyse
  ```
- **PHP-CS-Fixer** :
  Corrigez le style de code avec :
  ```bash
  vendor/bin/php-cs-fixer fix
  ```

---

## **Crédits**
Développé par **Becue Melvin** (Groupe E).