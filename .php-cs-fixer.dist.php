<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__);

$config = new PhpCsFixer\Config();

return $config
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setRules([
        '@PhpCsFixer' => true,
        '@PhpCsFixer:risky' => true,
        '@PHPUnit100Migration:risky' => true,
        '@PSR12:risky' => true,
        '@PHP83Migration' => true,
        '@PHP82Migration:risky' => true,
        '@DoctrineAnnotation' => true,
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@PER-CS' => true,
        '@PER-CS:risky' => true,

        'get_class_to_class_keyword' => true,
        'global_namespace_import' => true,
        'native_function_invocation' => false,
        'logical_operators' => true,
        'modernize_strpos' => true,
        'multiline_comment_opening_closing' => true,
        'no_alias_language_construct_call' => true,
        'no_homoglyph_names' => true,
        'no_superfluous_elseif' => true,
        'no_trailing_comma_in_singleline' => true,
        'no_unset_cast' => true,
        'nullable_type_declaration_for_default_null_value' => true,
        'php_unit_internal_class' => true,
        'php_unit_test_class_requires_covers' => true,
        'phpdoc_line_span' => true,
        'phpdoc_tag_type' => true,
        'self_static_accessor' => true,
        'single_line_comment_spacing' => true,
        'single_line_throw' => true,
        'static_lambda' => true,
        'types_spaces' => true,



        // reles witout set
        //'mb_str_functions' => true,
        // 'no_trailing_comma_in_singleline_array' => true, // deprecated
        'return_to_yield_from' => true,
        'attribute_empty_parentheses' => true,
        'ordered_attributes' => true,
        //'numeric_literal_separator' => true,
        // 'native_function_type_declaration_casing' => true, deprecated
        'final_class' => false,
        'final_public_method_for_abstract_class' => true,
        'ordered_interfaces' => true,
        'date_time_immutable' => true,
        'simplified_if_return' => true,
        'date_time_create_from_format_call' => true,
        //'phpdoc_to_param_type' => true,
        //'phpdoc_to_property_type' => true,
        //'phpdoc_to_return_type' => true,
        'regular_callable_call' => true,
        //'group_import' => true,
        //'class_keyword' => true,
        'php_unit_attributes' => true,
        'phpdoc_param_order' => true,
        'phpdoc_tag_casing' => true,
        'simplified_null_return' => true,
        'heredoc_closing_marker' => true,
        //'compact_nullable_typehint' => true, // deprecated
        // 'no_spaces_inside_parenthesis' => true, // deprecated

        //'strict_param' => true,
        'array_syntax' => ['syntax' => 'short'],
        'statement_indentation' => true,
        'explicit_string_variable' => true,
        'single_quote' => true,
        'multiline_whitespace_before_semicolons' => ['strategy' => 'no_multi_line'],
        'no_empty_statement' => true,
        //'braces' => ['position_after_functions_and_oop_constructs' => 'same'],
        'encoding' => true,
        'no_multiple_statements_per_line' => true,
        'declare_parentheses' => true,

        'combine_consecutive_unsets' => true,
        'declare_strict_types' => false,
        'dir_constant' => true,
        'native_constant_invocation' => false,
        'ereg_to_preg' => true,
        'heredoc_to_nowdoc' => true,
        'linebreak_after_opening_tag' => true,
        'mb_str_functions' => false,
        'modernize_types_casting' => true,
        'blank_lines_before_namespace' => false,
        'no_php4_constructor' => true,
        'echo_tag_syntax' => ['format' => 'short'],
        'no_unreachable_default_argument_value' => true,
        // TODO return in else will go out from else
        'no_useless_return' => true,
        'not_operator_with_space' => false,
        'not_operator_with_successor_space' => false,
        // TODO Moves inherited methods by order, but not attached to top
        'phpdoc_add_missing_param_annotation' => ['only_untyped' => true],
        // TODO @throws before @return (Requires update of most phpDocs and code style)
        //'phpdoc_order' => true,
        'pow_to_exponentiation' => true,
        'protected_to_private' => false,
        'psr_autoloading' => true,
        // TODO rand -> random_int & getrandmax -> mt_getrandmax, srand -> mt_srand
        //'random_api_migration' => ['rand' => 'random_int'],
        // TODO return null; -> return;
        //'simplified_null_return' => true,
        // TODO == -> ===
        'strict_comparison' => false,
        // TODO $strict argument of built-in functions
        'strict_param' => false,
        'ternary_to_null_coalescing' => true,

        // Symfony
        'binary_operator_spaces' => [
            'operators' => [
                '=' => 'single_space',
                '=>' => 'single_space',
            ],
        ],
        'blank_line_after_opening_tag' => true,
        'cast_spaces' => true,
        'concat_space' => ['spacing' => 'one'],
        'declare_equal_normalize' => ['space' => 'none'],
        'type_declaration_spaces' => true,
        'single_line_comment_style' => ['comment_types' => ['asterisk', 'hash']],
        'include' => true,
        'yoda_style' => ['equal' => false, 'identical' => false, 'less_and_greater' => false],
        'non_printable_character' => true,
        'lowercase_cast' => true,
        'class_attributes_separation' => [
            'elements' => [
                'method' => 'one',
            ],
        ],
        'native_function_casing' => true,
        'new_with_parentheses' => true,
        'no_alias_functions' => true,
        'no_blank_lines_after_class_opening' => true,
        'no_blank_lines_after_phpdoc' => true,
        'no_empty_comment' => true,
        'no_empty_phpdoc' => true,
        'no_leading_import_slash' => true,
        'no_leading_namespace_whitespace' => true,
        'no_mixed_echo_print' => ['use' => 'echo'],
        'no_multiline_whitespace_around_double_arrow' => true,
        'no_short_bool_cast' => true,
        'no_singleline_whitespace_before_semicolons' => true,
        //'no_spaces_around_offset' => ['inside', 'outside'],
        'no_unused_imports' => true,
        'no_whitespace_before_comma_in_array' => true,
        'no_whitespace_in_blank_line' => true,
        // NOTE Replaces "{" and "}" with "[" and "]" for strings
        'normalize_index_brace' => true,
        'object_operator_without_whitespace' => true,
        'php_unit_fqcn_annotation' => true,
        'phpdoc_align' => false,
        'phpdoc_annotation_without_dot' => true,
        'phpdoc_indent' => true,
        // TODO @inheritdoc -> {@inheritdoc} (Requires update of most phpDocs and code style)
//    'general_phpdoc_tag_rename' => [
//        'replacements' => ['inheritDocs' => 'inheritDoc'],
//        'fix_inline' => false,
//    ],
        'phpdoc_no_access' => true,
//    'phpdoc_no_alias_tag' => [
//        'type' => 'var',
//        //'link' => 'see',
//    ],
//    // NOTE Remove @return void and @return null (Requires update of all phpDocs and code style)
        'phpdoc_no_empty_return' => false,
        'phpdoc_no_package' => true,
        // NOTE Removes inheritdoc-only phpdocs
        'phpdoc_no_useless_inheritdoc' => false,
        'phpdoc_scalar' => true,
        'phpdoc_separation' => true,
        'phpdoc_single_line_var_spacing' => true,
        'phpdoc_summary' => false,
        'phpdoc_to_comment' => false,
        'phpdoc_trim' => true,
        'phpdoc_types' => true,
        'phpdoc_var_without_name' => true,
        'return_type_declaration' => ['space_before' => 'none'],
        // TODO Replaces Foo::bar() to self::bar() instead of static::bar()
        'self_accessor' => true,
        'short_scalar_cast' => true,
        'error_suppression' => true,
        //'single_blank_line_before_namespace' => true,
        'space_after_semicolon' => true,
        'standardize_not_equals' => true,
        'ternary_operator_spaces' => true,
        'trailing_comma_in_multiline' => [
            'elements' => ['arrays'],
        ],
        'trim_array_spaces' => true,
        // NOTE Removes space between "- $foo" when it placed to show that value will be negative
        'unary_operator_spaces' => true,
        'whitespace_after_comma_in_array' => true,
        'blank_line_before_statement' => [
            'statements' => ['break', 'case', 'continue', 'declare', 'default', 'do', 'exit', 'for', 'foreach', 'goto', 'if', 'return', 'switch', 'throw', 'try', 'while', 'yield'],
        ],
        'method_chaining_indentation' => true,
        'fully_qualified_strict_types' => true,
    ])
    ->setFinder($finder);
