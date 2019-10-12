# Searchable
```
composer require dillingham/searchable
```

- add `Searchable` to model(s)
- add `$table->longText('search');` to migration
- add `Model::searchIndex()` to migration
- add `->appendSearch($content)` to observer

example

```php
// on post save
$post->appendSearch($post->content);

// on comment save
$comment->post->appendSearch($comment->body);
```