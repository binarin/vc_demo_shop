vc_demo_shop
============

    CREATE TABLE orders(
      id serial primary key,
      amount float not null,
      status text not null default 'new',
      notifications text
    );
