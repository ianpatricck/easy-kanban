# Easy Kanban

## A Simple Kanban API

### Clone repository

```bash
$ git clone https://github.com/ianpatricck/easy-kanban
```

### Install dependencies via composer

```bash
$ composer install
```

### Run the migrate script

```bash
$ composer db:migrate
```

### Start the http server

```bash
$ composer serve
```

Now you can see the API documentation at http://localhost:8000/.

### Execute tests (PHPUnit)

```bash
$ composer test
```

Test execution will delete all rows in `development.db` file.
