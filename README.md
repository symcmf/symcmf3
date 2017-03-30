Symfony3 CMF
========================

## Setup development environment with Homestead 

1. Clone project

    with SSH

    ```
        git clone git@github.com:symcmf/symcmf3
    ```
    
    or with HTTPS
    
    ```
        git clone https://github.com/symcmf/symcmf3
    ```
	
2. Run composer
   
       ```
       composer install 
       ```
3. Setup homestead/vagrant environment in project folder:
	
    ```
    ./vendor/bin/homestead make
	```

4. Edit Homestead.yaml:
    > Remove the following lines from Homestead.yaml if you don't have this SSH keys on your machine (https://laravel.com/docs/master/homestead#installation-and-setup):
	> Or generate and paste your SSH keys.
    
    ```
    authorize: ~/.ssh/id_rsa.pub
    keys:
        - ~/.ssh/id_rsa
     ```
     
     > Set type option that tells Homestead to use the Symfony nginx configuration.
     
     ```
    sites:
        - map: homestead.app
          to: "/home/vagrant/yourprojectfolder/web"
          type: symfony
    ```

5. Run vagrant
	
    ```
    vagrant up
    ```
    
6. Generate the SSH keys :
    > For Symfony3+, no need of the -p option
     ```
       $ mkdir -p var/jwt 
     ```
    > Generate private and public key. This command will ask you to enter 
     "pass_phrase". You have to enter this password in parameters.yml file
     (jwt_key_pass_phrase)

       $ openssl genrsa -out var/jwt/private.pem -aes256 4096
       $ openssl rsa -pubout -in var/jwt/private.pem -out var/jwt/public.pem
     ```
    
   
7. After it, browse [http://192.168.10.10](http://192.168.10.10), you should see the main page of application.
   Or add to your hosts file 
    
     ```
        192.168.10.10  homestead.app
     ```
   
     and browse [http://homestead.app](http://homestead.app).
     