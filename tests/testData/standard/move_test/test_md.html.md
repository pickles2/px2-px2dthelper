<p>test</p>
<ul>
    <li><a href="./test_broccoli.html">A link</a></li>
    <li><a href="./test_broccoli.html?a=b">A link</a></li>
    <li><a href="./test_broccoli.html#foobar">A link</a></li>
    <li><a href="./test_broccoli.html?a=b#foobar">A link</a></li>
</ul>

- [A link](./test_broccoli.html)
- [A link](./test_broccoli.html?a=b)
- [A link](./test_broccoli.html#foobar)
- [A link](./test_broccoli.html?a=b#foobar)

<!-- autoindex -->

```html
<a href="./test_broccoli.html">A link in code block</a>
```

<!--
<a href="./test_broccoli.html">A link in HTML comment out</a>
-->

<?php
echo '<a href="./test_broccoli.html">A link in PHP block</a>';
?>

```bash
# Bash Code
git fetch;
git checkout -b "feature/test_branch_1";
git checkout -b "feature/test_branch_2"; // This is a new branch
git checkout -b "feature/test_branch_3";
git checkout -b "feature/test_branch_4";
git checkout -b "feature/test_branch_5";
```

paragraph <?= 'test' ?> message.

paragraph <a href="<?= $px->href("test") ?>">test</a> message.

```php
<<?= '' ?>?php

// PHP Code
echo '<a href="./test_broccoli.html">A link in PHP block in code block</a>';
```


```php
<<?php ?>?php

// PHP Code
echo '<a href="./test_broccoli.html">A link in PHP block in code block</a>';
```
