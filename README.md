# softwareseni

The plugin work flow is as below :
1.  Call plugin activation function by register_activation_hook() which calls function install_testimonial().
    If it is the first time installation, it will automatically create database for the testimonial data.
2.  It will show the HTML form code to the user by the call of shortcode [ss-testimonial] in the post page by the method of html_form_code().
3.  On user submit, it will call the method save() inside class SS_Testimonial.
4.  The Admin page menu will show in the dashboard by the method my_admin_menu() inside WP_Testimonial_Admin class. The Admin page will show by the method admin_page().
5.  The random user testimonial will be displayed in the widget by the class Testimonial Widget. First, it will register itself to the Wordpress Widget list using the method register_testimonial_widget(). The widget will show random testimonial data by the method widget() (works as front-end) which fetch data by the method form() (works as back end).
