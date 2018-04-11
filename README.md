
# Configuration
Create a file named `.env.php`, in which you put the enviroment variables `BASE_URL` and `AUTH`.
```php
putenv('BASE_URL=https://SOMETHING.atlassian.net/');
putenv('AUTH=SECRET_KEY');
```
On `.config.php` you may declare any particular behaviour
* `$categories` Specifies which proyect's categories will be taken in mind for the report
* `$legacy` and `$on_hold` Specifies a status that is decided manually

Be sure to fill `atlassian-connect.json` [according to Jira](https://developer.atlassian.com/cloud/jira/platform/app-descriptor/)

Any request, I'll be happy to help :)
