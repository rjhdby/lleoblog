# lleoblog

�����������

1. �����

    �������� ������ � ������ ����� ��� ���������� �����, ������������ config.sys.tmpl � config.sys and � ��������������. ����� ������ �������� - ��������� MySQL:

        $msq_host = "localhost";
        $msq_login = "root";
        $msq_pass = "MySIC1234";
        $msq_basa = "blogs";
        $msq_charset = "cp1251";

    ���� ������ ���������� �� � ������, � ���������� ����� (�������� blog/):
    -- ��������� $blogdir = "blog/"; � config.sys
    -- ���� ������������ apache, ��������� � .htaccess "RewriteBase /dnevnik/" ������ "RewriteBase /"

    ���� ������������ nginx, �� ������:
	-- ��������� ������ � ����� hidden
	-- �������������� ������ ����� �������������� ������� �� index.php
	-- ������ ������������ nginx.cong � �����

2. ������

    ���������� ������� http://���.����/install, ����������� �������� � �������� �����������. ������ ����� ����������� ��� ������ �������� ������.

3. ����� ���������

    ��������!!! ����� ���������� �� ����� ��������� ����� ��������� �����! ����� ��������� ������� ��������� ������:

    -- ��������� $admin_unics="99999999"; (�������������� �����) � config.sys
    -- ����������� http://���.����/install ������� �� ���������� "U" ��� �������� �� ������ � ������ ������� ���� ����� ������� ���� �������� � �������� � ��������� ����� (�������� 1)
    -- ������ ��� $admin_unics="1"; � config.sys
    -- ����������� �������� ����� ������� ������ ����� ����� ������ - ��� ��������� ����.


������ nginx.conf:

upstream home {
  server unix:/var/run/home-fpm.sock;
}

server {
  listen 80 default_server;
  listen [::]:80 default_server ipv6only=on;

  root /var/www/home;
  index index.php index.html index.htm index.shtml;

  server_name lleo.me;

  client_max_body_size 500M;

  location /hidden {
    deny all;
    return 404;
  }

  location / {
    try_files $uri /index.php?$args;

    access_log /var/www/home/hidden/nginx/access.log;
    error_log /var/www/home/hidden/nginx/error.log;

    location ~ \.php$ {
      fastcgi_split_path_info ^(.+\.php)(/.+)$;
      fastcgi_pass unix:/var/run/home-fpm.sock;
      fastcgi_index index.php;
      include fastcgi_params;
      proxy_set_header Host $host;
      proxy_set_header X-Real-IP $remote_addr;
      proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
      client_max_body_size       500m;
      client_body_buffer_size    128k;
      proxy_connect_timeout      90;
      proxy_send_timeout         90;
      proxy_read_timeout         90;
      proxy_buffer_size          4k;
      proxy_buffers              4 32k;
      #proxy_buffers           32 4k;
      proxy_busy_buffers_size    64k;
      proxy_temp_file_write_size 64k;
    }
  }
}
