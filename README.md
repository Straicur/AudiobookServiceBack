<p align="center"><img src="https://socialify.git.ci/Straicur/AudiobookServiceBack/image?description=1&amp;descriptionEditable=Manage%20and%20share%20audiobooks&amp;font=Source%20Code%20Pro&amp;language=1&amp;name=1&amp;pattern=Plus&amp;theme=Dark" alt="project-image"></p>

<h3>AudiobookServiceBack is the backend of my audiobook management application. It is a Rest Api that responds to requests from the front. It's my portfolio project, which includes a lot of examples of using the Symfony framework, which I want to develop and specialize in. This is a further implementation of the concept that i have used for our engineering work. It was created in the concept of MVC architecture. At first, it was supposed to be a regular system for managing audiobooks without the possibility of sharing it with other users and was supposed to be limited to only the administrator. However, the project has developed much more and is now an implementation of a system that could compete with other such services on the Internet (rather, at first on a much smaller scale).</h2>

<h2>💻 Built with</h2>

Technologies used in the project:

*   Php 8.1
*   Symfoy 6.4 (Rest Api)
*   Redis
*   MySql

<h2>🚀 Demo</h2>

[https://audiobookback.icu/api/doc](https://audiobookback.icu/api/doc)

<h2>🧐 Documentation</h2>
<details>
  <summary>Show</summary>
All files are saved on the server in the specified path (it has over-size protection on disk). Authentication is possible through a generated token, which must be added to the header for almost every request. Queries are sent as json, the application serializes them to objects accordingly and works on classes, not pure json. Errors are subject to logging with the monologue package and if any occur, the appropriate response 500, 400, 401, 403, 409 is returned. </br> For the audiobook to work properly, it must be provided in the appropriate zip format (mp3 files and cover JPG or PNG). When an error occurs, the audiobook can be deleted from the disk, and before activation, it is not visible to users. This also applies to the entire category and the audiobooks plugged into it. As an additional security measure, in addition to the Uuid itself for access to data sent from the api (audiobook details), a corresponding category key is required, created together with it. Translations have also been added, which, depending on the language or localization sent in the header, return a message to the user in the appropriate language (currently Polish and English). </br> Tests have also been added to check and catch errors that are returned respectively (you can start them with 'make tests'). With the addition of the make package, you can execute shell commands that have been prepared to set up the server faster and add basic data. They mostly execute prepared symfony commands and can be found in the Makefile. </br> A smpt server has also been added, which sends emails appropriate to the situation, which are also translated. Additionally, SMS API is used (Vonage). Also included are listeners and tools that encapsulate minor functionalities that are repetitive or need to be encapsulated in one place. implemented api swagger for easier work with api and describing appropriate endpoints. </br> Staged using OVH hosting on an Ubuntu system and using nginx, redis, phpfpm, PHP 8.1 and Symfony 5. 

## Functionalities
### Admin
You have to be an admin to do these things. Admins are added with special commands.
- Categories - Categories are like a tree. There can be a main category and a lot of subcategories. An audiobook needs to be in at least one category to be displayed (the user sees only category audiobooks). They need to be activated to be displayed. You can also change their names or delete them, and they sub categories.
- Audiobooks - Audiobooks are added in the appropriate zip file format, which consists of a folder and mp3 files and cover jpg or png, and then he can manage them like: listening, downloading zip and editing it, re-adding (there are options to delete notifications of this audiobook and to delete his comments), deleting and adding categories, and removing the audiobook from the category and the system.
- Users - Managing users, he can change them at least: role, phone, password, unban, and activate them. An additional option for the user is a request for account deletion, which is also handled by the administrator. In details, the administrator can see why he is banned and a period to.
- Notifications - Notifications are a simple implementation of notifying a user about many things going on in the system, like: new audiobook or categories, a new proposed list, accepting or rejecting his report, or just to say something to users.
- Technical breaks - Technical breaks are here to secure a system. When one is active, a user can't do anything in a system, only admins can operate on it and prepare it to work.
- Reports - Reports are a proper thing to report bugs, strange user behavior, or just to ask about anything. The administrator can respond to them, accept them or reject them.
- Cache - Cache is implemented with a Redis. Admin needs to clear it to work or test a system. With that, he does not need to wait until a redis key time of live is expired. It also clears the front cache of useQuery and local storage.
- Statistics - There are simple statistics to see what is going on with the service.

### User
- Authorize - You need to enter a valid email and password to get an API key. With an API key, the system knows who you are and it is used in almost every endpint.
- Register - Registration requires providing correct data that is not in the system, such as a phone number and email address. After receiving the appropriate data, the system will send an email with a face to the page that will activate the user by adding the appropriate role in the system. The user also has the option to impose parental controls.
- Comment - After listening to more than half of the audiobook, the user has the option to comment on it or like other comments. The depth of the comments is shallow and only appears as the main comment and comments to the main one.
- Notifications - Notifications are returned to a list, and they are very different depending on who is doing what in the system. The number of new notifications is returned and the list shows which one has not been seen yet (hovered in the fort). Notifications contain descriptions and various details depending on the type.
- Audiobooks - The user receives a list of categories and in each category its audiobooks that are active. In the list, these are basic data but can also download its full details and its cover and mp3 file.It is also possible to search for audiobooks based on their name or authors.
- Proposed - After listening to more audiobooks or adding some to your favorites list, the system creates a list of suggested audiobooks that are suggested based on the categories you listen to. However, audiobooks from only one category are not returned, but from several, so the suggested audiobooks are more interesting and more diverse.
- MyList - This is a list of your favorite audiobooks that allows for quicker access to the audiobooks that interest you.
- Report - The user has the ability to report various problems in the system and display a list of their reports to have insight into them. Upon acceptance or rejection, they will receive an email or notification.
- Settings - The settings allow you to reset your password, change your password with email acceptance, change your email with email acceptance and SMS code, impose parental controls, and delete your account on the website.

</details>

<h2>🛠️ Installation Steps:</h2>

<p>1. Install all Php 8.1 dependencies</p>

<p>2. Install MySql server and redis</p>

<p>3. Install maker</p>

<p>4. Clone project and create a .env.local file and complete the file with the appropriate data based on the .env file. You need to remember about secrets like MAILER_DSN (I was using a gmail) and smsapi SMS_KEY and SMS_SECRET for Vonage. These are free versions so you can set them up. Without them, the system will not work properly in all places </p>

<p>5. Do a command</p>

```
make install
```

<p>6. Run a redis</p>

```
docker compose up
```

<p>7. Run a symfony project</p>

```
symfony serve or php bin/console serve
```


<h2> 💣 Running Tests</h2>

To run tests, run the following command

```bash
  make tests
```

<h2>🛡️ License:</h2>

This project is licensed under the GNU LESSER GENERAL PUBLIC LICENSE Version 2.1 February 1999
