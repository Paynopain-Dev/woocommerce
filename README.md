# Paylands WooCommerce Plugin v2.0.1

This plugin adds a **Paylands** Payment Option in WooCommerce plugin for WordPress.

## Setup

By using Docker we avoid having to manually install and tear down the WordPress environment (Web server and MySQL database).

On first run, download a compatible WooCommerce version and uncompress it inside the `/plugins` folder. The Paylands WooCommerce plugin will also be installed automatically based on the plugin on "/src". Once ready, run the following command to install WordPress and its database on a docker container:

    sudo docker-compose up --build -d 

If you use it this port 8000 for something with docker you will need to stop it and destroy it. First of all check which ID have this container:

    sudo docker container ls -a

When you have clear what container you need just stop it and crash it too:

    sudo docker stop [container ID]
    sudo docker rm   [container ID]

After a few seconds, the WordPress installation will be accessible to be configured on the following URL:

    http://localhost:8000/

Once the WordPress installation is configured, login and from the Dashboard enable the WooCommerce plugin by going to `Plugins > WooCommerce > Activate`. Follow these instructions on each step:
1. Fill in the required fields.
2. Deselect Stripe and Paypal as payment options.
3. Select "Free shipping" on both the main location and other zones. Disable the "Print shipping labels at home" option.
4. Click on "Skip this step".
5. Click on "Skip this step".
6. Click on "Create a product".
    1. Give the product a **Name** and a **Regular price**.
    2. Click the "Publish" button on the right.
    
You can now browse the store on the following URL:

    http://localhost:8000/?post_type=product

## Trying out different versions of WordPress and WooCommerce

To try out a different version of WordPress, open `.docker/Dockerfile` and change the following line to the version you want to install:

    FROM wordpress:5.2.1

Also download the version of WooCommerce from [here](https://developer.woocommerce.com/releases/). Unzip the file and move the `woocommerce` folder into the `/plugins` folder.

Once this is done, run the following command to remove the WordPress installation and its database. This will delete any products you may have created.

    docker-compose down --volumes

Finally, follow the steps on the `Setup` section to install WordPress and the WooCommerce plugin again.

## Debugging errors

If you encounter an error, you may need to enable WordPress debugging manually so the trace of the error is shown. To do so, open a console into the WordPress installation by running the following command:

    docker exec -it woocommerce-plugin sh

Once inside, you can edit the file `wp-config.php` using `vim` and change the following line, setting the value to **true**.

    define( 'WP_DEBUG', false );

## Compatibility

These are all pairs of versions of WordPress and WooCommerce that have been tested, and whether they work or not:

| WordPress | WooCommerce | Compatibility|
|-----------|-------------|--------------|
| 5.2.1     | 3.5.3       | Yes          |
 
