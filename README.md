# endocore-phtml-renderer

PHtml Renderer component for the Endocore framework.

## Usage

### Output

```
{{ $foo }}

{{ $foo|raw }}
```

### Conditions

```
{% if ($foo === true) %}
    ...
{% elseif ($bar === true) %}
    ...
{% else %}
    ...
{% endif %}
```

### Loops

```
{% foreach ($items as $item) %}
    ...
{% endforeach %}
```


### Includes

```
{{ include('some/view') }}
```

## Todo

- Loops
  - For Loop
  - Loop-Else (`forelse`)
- Extends/Inheritance
- Caching