# Manual de comandos

En éste documento se describen todos los comandos y cambios a ficheros necesarios
para seguir el curso de [Docker Para Entornos de Desarrollo](https://www.udemy.com/course/docker-para-entorno-de-desarrollo-web/?referralCode=63DB460320F0C03A1E87).

Si hay cualquier mejora o sugerencia, no dudes en abrir un ticket en el repositorio,
o mejor aún clonar el repositorio, hacer cambios y crear un Pull Request.

## Para después del curso

Empezamos por el final. Cuando acabes el curso, quizás quieres saber cómo continuar con tu
viaje en el mundo de Docker. Para ello voy escribiendo artículos en mi [Blog](https://krenel.org/recursos-interesantes-para-el-curso-de-docker-para-entornos-de-desarrollo/?ref=bitbucket).

Allí se menciona el canal de Discord donde podemos hablar de tú a tú, resolver dudas, y
proponer cambios en el curso, o sugerir nuevos cursos. Si te interesa, no dudes en unirte al club. 


## Sección 2 - Instala dependencias de Laravel

### Clonando el proyecto de Laravel 5.0

	# Creamos un directorio donde trabajar (opcional)
	mkdir -p ~/udemy && cd ~/udemy
	# Clonamos Laravel 5.0
    git clone -b 5.5 https://github.com/laravel/laravel.git captacion
	# entramos en el directorio
    cd ./captacion

### Instalando las dependencias con composer

	docker run --rm -it -v "$(pwd)":/app composer composer install


#### Nota para usuarios de Windows:

Debido a que las variables funcionan algo diferente en Windows que en Linux/OSX el comando es diferente:

	docker run --rm -it -v ${PWD}:/app composer composer install
    
Todas las ocurrencias durante ésta guía de `"$(pwd)"` deberán ser cambiadas por `${PWD}` si utilizas Windows.


## Sección 3 - Arranca el proyecto

### Descargar la imagen de PHP

Se recomienda encarecidamente a no usar "php" o "php:latest" ya que podéis tener problemas con dependencias después. La última versión siempre aumenta, y por tanto aumenta el riesgo de que no sea compatible hacia atrás. El curso se hizo con php7.2 (creo recordar) y había hasta la versión 7.3, y funciona todo bien:

	docker pull php:7.3

### Instala dependencias de Laravel con Dockerfile

> **¡Cuidado! El fichero ha cambiado con respecto al del curso. Nótese que se especifica la versión, se el nombre del cliente de MySQL ha cambiado y "zip" requiere de una dependencia adicional.**

Fichero que se tiene que guardar con nombre "Dockerfile" en la raíz del proyecto.

	
	FROM php:7.3

	RUN apt-get update && apt-get install -y --no-install-recommends \
	libmcrypt-dev \
	default-mysql-client \
	libmagickwand-dev

	RUN pecl install imagick
	RUN docker-php-ext-enable imagick
	RUN docker-php-ext-install pdo_mysql
	RUN apt-get install -y --no-install-recommends libzip-dev
	RUN docker-php-ext-install zip

	WORKDIR /app

#### Haz la build de la imagen

	docker build -t captacion-php ./

### Levantar el proyecto de Laravel

	docker run --rm -it -p 8000:8000 -v "$(pwd)":/app captacion-php php artisan serve --host=0.0.0.0

## Sección 4 - Configura Laravel

### Copiar el fichero .env

	 cp .env.example .env

### Generar una APP_KEY

	docker run --rm -it -p 8000:8000 -v "$(pwd)":/app captacion-php php artisan key:generate

## Sección 5 - Presentación web

Como ejemplo, se utiliza el proyecto [Bootstrap-coming-soon](https://github.com/BlackrockDigital/startbootstrap-coming-soon). Para poder usarlo, tienes que copiar ficheros de éste repositorio al repositorio de Laravel:

    .
    └── coming-soon-template
        ├── Coming\ Soon\ -\ Start\ Bootstrap\ Theme_files
        │   ├── all.min.css
        │   ├── bootstrap.bundle.min.js
        │   ├── bootstrap.min.css
        │   ├── coming-soon.min.css
        │   ├── coming-soon.min.js
        │   ├── css
        │   ├── css(1)
        │   └── jquery.min.js
        ├── fa-brands-400.woff2
        ├── mp4
        │   └── bg.mp4
        └── welcome.blade.php

Copia todos los ficheros del directorio coming-soon-template dentro del directorio `./public` del proyecto.

Ejecuta el siguiente comando en la raíz del proyecto:

	mv public/welcome.blade.php resources/views/welcome.blade.php



## Sección 6 - Presentación de Caso Real

### docker-compose.yml

El fichero se tiene que guardar a la raíz del proyecto:

	version: '3.0'

	services:
	  web:
        image: 'captacion-php'
        ports:
          - '8000:8000'
        volumes:
          - "./:/app"
        command: php artisan serve --host=0.0.0.0

### Iniciar el proyecto con compose

	docker-compose up


## Sección 7 - Instala MySQL

### Actualizar docker-compose.yml

    version: '3.0'
    
    services:
      web:
        image: 'captacion-php'
        ports:
          - '8000:8000'
        volumes:
          - "./:/app"
        command: php artisan serve --host=0.0.0.0
    
      mysql:
        image: 'mysql:5.7'
        ports:
          - '3306:3306'
        environment:
          - MYSQL_ROOT_PASSWORD=toor
        volumes:
          - mysql-data:/var/lib/mysql
    
    volumes:
      mysql-data:

### Volver a lanzar para levantar MySQL

	docker-compose up

### Crear la base de datos de captación

> Si no puedes ejecutar éste comando o no tienes una aplicación instalada para conectarte, no te preocupes. Más adelante prepararemos un comando hay un comando que creará la BBDD si no la has creado ya.

	create database captacion;

### Hacer links entre containers

> **¡Cuidado! Éste paso ya no es requerido. Todo lo que haya en docker-compose estará lincado por defecto. Es más, en nuevas versiones de DockerCompose se deprecata el uso de Link, de modo que es mejor no usarlo**

> **Digamos que el equipo de docker simplificó ésta parte y ya no hace falta.**


### Cambios en el .env

    DB_CONNECTION=mysql
    DB_HOST=mysql
    DB_PORT=3306
    DB_DATABASE=captacion
    DB_USERNAME=root
    DB_PASSWORD=toor



## Sección 8 - Conectando Containers

	# Conectarse al container
	docker-compose exec web bash

Por si antes no has creado la base de datos. Si ya habías creado la BBDD puedes lanzar el comando y no romperás nada.

	mysql -uroot -ptoor -hmysql -e 'create database IF NOT EXISTS captacion'

Comprueba el estado de las migraciones

	# Mirar el estado de las migraciones (dará error)
	php artisan migrate:status
	# Lanzar las migraciones
	php artisan migrate
	# Mirar el estado de las migraciones (funcionará)
	php artisan migrate:status

Modo alternativo de lanzar los comandos (no hace falta ejecutarlo):

	docker-compose exec web /app/artisan migrate:status

### Conectar en mysql

Entra en el container de MySQL y conectar al servidor de MySQL

	docker-compose exec mysql bash
	mysql -uroot -ptoor

Los dos pasos anteriores, a la vez:
	
	docker-compose exec mysql mysql -uroot -ptoor


	
## Sección 9 - Cerrando la funcionalidad de la aplicación

> No hace falta que hagas todos los pasos de ésta sección si no quieres. Son muy específicos de Laravel y no aportan tanto valor. Lo que aporta valor es ver cómo funciona todo y encajan las piezas, o sea, viendo los vídeos. Aún así, si te sientes con ganas de replicar los vídeos, vamos a ello.

> En ésta sección seré especialmente verbose explicando por qué haga cada uno de los puntos para que se entienda bien por qué hacemos lo que hacemos. Si conoces Laravel será aburrido. Si no lo conoces será confuso. Otra vez, no hace falta que sigas éstos pasos si no quieres.

### Crear una Migración para crear una nueva tabla

Necesitamos crear una tabla en MySQL para guardar los emails. Una forma de hacerlo es crear una Migration de Laravel. Crea un fichero en `./database/migrations/2020_03_26_221404_create_table_emails.php` con el contenido:

    <?php  
      
    use Illuminate\Support\Facades\Schema;  
    use Illuminate\Database\Schema\Blueprint;  
    use Illuminate\Database\Migrations\Migration;  
      
    class CreateTableEmails extends Migration  
    {  
      /**  
     * Run the migrations. * * @return void  
     */  public function up()  
     { Schema::create('emails', function (Blueprint $table) {  
      $table->increments('id');  
      $table->string('email', 100);  
      $table->timestamps();  
      });  
      }  
      
      /**  
     * Reverse the migrations. * * @return void  
     */  public function down()  
     { Schema::dropIfExists('emails');  
      }  
    }
	
	
Podemos ver las migraciones pendientes que hay, y ahora ésta debería aparecer en el listado como no ejecutada:

	docker-compose exec web /app/artisan migrate:status

La salida del comando debería ser:

    +------+------------------------------------------------+
    | Ran? | Migration                                      |
    +------+------------------------------------------------+
    | Y    | 2014_10_12_000000_create_users_table           |
    | Y    | 2014_10_12_100000_create_password_resets_table |
    | N    | 2020_03_26_221404_create_table_emails          |
    +------+------------------------------------------------+

Podemos ejecutar la migración (creación de la tabla) ejecutando:

	docker-compose exec web /app/artisan migrate

El output del comando debería ser:

    Migrating: 2020_03_26_221404_create_table_emails
    Migrated:  2020_03_26_221404_create_table_emails

Si vuelves a ejecutar el "status" verás que ya no quedan migraciones por ejecutar. La tabla ha sido creada:

    docker-compose exec web /app/artisan migrate:status
    +------+------------------------------------------------+
    | Ran? | Migration                                      |
    +------+------------------------------------------------+
    | Y    | 2014_10_12_000000_create_users_table           |
    | Y    | 2014_10_12_100000_create_password_resets_table |
    | Y    | 2020_03_26_221404_create_table_emails          |
    +------+------------------------------------------------+
    	
> ¿Todo esto se podría haber arreglado con un "create table blahblah"? Sí, al 100%. Pero ésta es la forma de hacerlo con Laravel. La ventaja de hacer éstos ficheros es que los commiteas en el repositorio, y si un compañero se descarga el repositorio basta que ejecute las migrations con el comando anterior y aplicará los cambios en el modelo. Así todo el mundo tiene la última versión de todo, y está en el Sistema de Control de Versiones.

### Crea el fichero modelo Email

Crea el fichero `./app/Email.php` con el contenido:

    <?php  
      
    namespace App;  
      
    use Illuminate\Database\Eloquent\Model;  
      
    class Email extends Model  
    {  
      protected $table = 'emails';  
      
      protected $fillable = [  
      'email'  
      ];  
    }

Éste fichero define el objeto del ORM (Eloquent). Por esto define el nombre de la tabla que acabamos de crear, así como que tiene un campo `email`.

### Crea una ruta para la URL "/email"

> ¡Cuidado! En pro a la simplificación haremos una pequeña ñapa. Lo suyo sería crear una controladora, registrar la ruta, etc... Pero es mucho jaleo. Mejor simplificamos.

Editamos el fichero `./routes/web.php` con el contenido:

    <?php  
      
    /*  
    |--------------------------------------------------------------------------  
    | Web Routes  
    |--------------------------------------------------------------------------  
    |  
    | Here is where you can register web routes for your application. These  
    | routes are loaded by the RouteServiceProvider within a group which  
    | contains the "web" middleware group. Now create something great!  
    |  
    */  
      
    use Illuminate\Http\Request;  
      
    Route::get('/', function () {  
      return view('welcome');  
    });  
      
    Route::get('/email', function(Request $request) {  
      $email = App\Email::updateOrCreate($request->all());  
      return sprintf('Thanks for submitting your email, %s <a href="/">home</a>', $email->email);  
    });

Si nos centramos en el último bloque, así muy rápido, la primera línea de la función lee todos los parámetros que nos han pasado por GET, y utiliza el modelo del ORM Email para crear un nuevo registro. La sintaxis es muy compacta (y un infierno cuando quieres hacer un test unitario, pero esto ya es otra historia para otro día).

La segunda línea imprime un mensaje guarro conforme la operación se ha realizado con éxito.

### Utilizando el formulario

Si utilizamos el formulario se inserta un nuevo registro.

Para ver si un email se ha guardado en la base de datos correctamente podemos ejecutar el siguiente (horrendo) comando:

    docker-compose exec web mysql -hmysql -ptoor captacion -e'select * from emails;'
    +----+----------------------+---------------------+---------------------+
    | id | email                | created_at          | updated_at          |
    +----+----------------------+---------------------+---------------------+
    |  1 | contact@jcarreras.es | 2020-03-26 22:43:02 | 2020-03-26 22:43:02 |
    +----+----------------------+---------------------+---------------------+

Aunque es uno de los comandos más complejos y no se explica explícitamente en el curso, ya debería saber diseccionar todas las partes y saber qué parámetros son de docker-compose, cuales se ejecutan dentro del container, qué parámetros se le pasan al cliente de MySQL y qué parte es la query. Complejo? Si. Pero entendible y reproducible en cualquier máquina.

### Configurando mailtrap

Añade el siguiente bloque a `docker-compose.yml`:


    mailtrap:
    	image: 'eaudeweb/mailtrap'
    	ports:
    		- "8081:80"


Reinicia el stack de docker compose:

	docker-compose down
	docker-compose up

Ahora deberías poder entrar en http://localhost:8081 con el usuario `mailtrap` y contraseña `mailtrap`. 

### Adapta la aplicación para que mande el correo

Modifica el fichero `./routes/web.php` y adapta ésta función, añadiendo las líneas de `\Mail::raw(...`:

    Route::get('/email', function(Request $request) {  
      $email = App\Email::updateOrCreate($request->all());  
      
      \Mail::raw('You have been subscribed', function($message) use ($email) {  
	      $message->to($email->email);  
      });  
      
      return sprintf('Thanks for submitting your email, %s <a href="/">home</a>', $email->email);  
    });

Con éste cambio, cuando un usuario se suscriba a las notificaciones, le mandaremos inmediatamente un correo.

### Ajustar el .env

Modifica el fichero .env, el bloque de `MAIL_*` para que quede tal que:

    MAIL_DRIVER=smtp  
    MAIL_HOST=mailtrap
    MAIL_PORT=25  
    MAIL_USERNAME=null  
    MAIL_PASSWORD=null  
    MAIL_ENCRYPTION=null

Para que los cambios del .env sean efectivos, deben reiniciar el Stack:

	docker-compose down
	docker-compose up


# Imagenes usadas
https://hub.docker.com/_/composer
https://hub.docker.com/_/php
https://hub.docker.com/r/eaudeweb/mailtrap


https://github.com/laravel/laravel
https://bitbucket.org/jcarreras/curso-docker-para-entornos-de-desarrollo/src/master/
