vc_demo_shop
============

    CREATE TABLE orders(
      id serial primary key,
      amount float not null,
      status text not null default 'new',
      notifications text
    );

    heroku config:set VC_URL=http://.../pay VC_SERVICE_ID=... VC_SECRET='...'
    heroku config:set PUBLIC_URL='http://vc-demo.herokuapp.com/'
