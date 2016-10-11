/**
 * Root definitions
 */
Root = _? first:Statement rest:(_? Statement)* _? { return Util::combine($first, $rest, 1); }
  Statement = BlockStatement / LineStatement
    BlockStatement = Cond / Loop
    LineStatement = stmt:(Set / CommandList) _? ';' { return $stmt; }

StatementSep
= _? (';' / "\n") _?

/**
 * Misc
 */
SEP = ':'
_ "WhitespaceOrComment" = (Whitespace / Comment)+
Comment = '#' [^\r\n]*
Whitespace = WhitespaceChar+
WhitespaceChar = [ \t\n\r]


/**
 * Quoted
 */
BacktickQuoted = '`' str:$BacktickQuotedChar* '`' { return $str; }
BacktickQuotedChar
  = '\\`' { return '`'; }
  / chr:[^`] { return $chr; }
WildCardValue = chunks:WildCardChunk* { return Command\Elasticsearch::escapeQuery($chunks); }
WildCardChunk
  = '*' { return Command\Elasticsearch\Token::W_STAR; }
  / '?' { return Command\Elasticsearch\Token::W_QMARK; }
  / DoubleQuoted
  / SingleQuoted
  / Escaped
DoubleQuoted = '"' str:$DoubleQuotedChar* '"' { return $str; }
SingleQuoted = "'" str:$SingleQuotedChar* "'" { return $str; }
Escaped = $EscapedChar+
DoubleQuotedChar
  = '\\"' { return '"'; }
  / SpecialChar
  / [^"]
SingleQuotedChar
  = "\\'" { return "'"; }
  / SpecialChar
  / [^']
EscapedChar
  = '\\ ' { return ' '; }
  / '\\"' { return '"'; }
  / "\\'" { return "'"; }
  / '\\(' { return '('; }
  / '\\)' { return ')'; }
  / [^;'" *?()]
SpecialChar
  = '\\n' { return "\n"; }
  / '\\r' { return "\r"; }
  / '\\t' { return "\t"; }
RegexVal = $RegexChar*
RegexChar
  = '\\/' { return '/'; }
  / [^/]


/**
 * Field names and values
 */
Value = $[a-zA-Z0-9\._\-]+
Var = '$' var:$[a-zA-Z0-9_]+ { return new Symbol($var); }
PathVar = '$' var:$[a-zA-Z0-9_]+ list:PathList { return new Symbol($var, Symbol::T_LIST, $list); }
  PathList
    = list:('.' $[a-zA-Z0-9_]+)+ { return Util::pluck($list, 1); }
    / '.' { return []; }
IntVar = Var / Integer
ExprVar = Var / BacktickQuoted
ValVar = Var / Value
KeyVar = Var / Key
PrimVar = Primitive / Key
WCValVar = Var / WildCardValue
Key = $[a-zA-Z0-9_\.\-]+
Boolean
  = 'true' { return true; }
  / 'false' { return false; }
Number = Float / Integer
Float = num:$([0-9]+ '.' [0-9]*) { return floatval($num); }
Integer = num:$[0-9]+ { return intval($num); }
Primitive = Boolean / Number / Value / DoubleQuoted / SingleQuoted / Arr
Arr = '[' first:Primitive rest:(_? ',' _? Primitive)* _? ','? _? ']' { return Util::combine($first, $rest, 3); }


/**
 * Statements
 */
CommandList = first:CommandPair rest:(_? '|' _? CommandPair)* { return new Statement\CommandList(Util::merge($first, $rest, 3)); }
  CommandPair = first:Command rest:(_? Store)? { $arr = [$first]; if(count($rest) > 0) $arr[] = $rest[1]; return $arr; }
    Command = SourceCommand / Map / Join / Sort / Head / Tail / Filter / Count
      SourceCommand = Elasticsearch / Load

Set = 'set' _ target:Key '=' val:Primitive { return new Statement\Set($target, $val); }

Cond = 'if' _ expr:BacktickQuoted _ '{' a:Root '}' b:(_ 'else' _ '{' Root '}')? { return new Statement\Cond($expr, $a, $b ? $b[4]:null); }

Loop = 'for' _ source:Key _ '{' root:Root '}' { return new Statement\Loop($source, $root); }

/**
 * Commands
 */
Elasticsearch = 'es' SEP source:Key opts:(_ ElasticsearchOpts)? _ query:ElasticsearchQuery agg:(_? '|' _? ElasticsearchAgg)? { return $this->es_builder->build($source, $query, count($agg) > 0 ? $agg[3]:null, $opts ? $opts[1]:[]); }
  ElasticsearchOpts = first:ElasticsearchOption rest:(_ ElasticsearchOption)* { return Util::assoc($first, $rest, 1); }
    ElasticsearchOption = '!' field:Key SEP val:PrimVar { return [$field, $val]; }
  ElasticsearchQuery
    = '*' { return ['match_all' => []]; }
    / ElasticsearchQueryOR
  ElasticsearchAgg = 'agg' SEP agg:(
    AvgAgg / CardinalityAgg / ExtendedStatsAgg / GeoBoundsAgg / GeoCentroidAgg / AvgAgg / MaxAgg / MinAgg / PercentilesAgg / PercentileRanksAgg / StatsAgg / SumAgg / TopHitsAgg / ValueCountAgg /
    DateHistogramAgg / DateRangeAgg / GeoDistanceAgg / GeoHashGridAgg / HistogramAgg / IPv4RangeAgg / MissingAgg / RangeAgg / SignificantTermsAgg / TermsAgg
  ) { return $agg; }

    AvgAgg = 'avg' _ key:KeyVar options:(_ AggOptions)? subagg:(_? '|' _? ElasticsearchAgg)? { return new Command\Elasticsearch\Agg\Metrics\Avg($key, $options ? $options[1]:[], $subagg ? $subagg[3]:null); }
    CardinalityAgg = 'cardinality' _ key:KeyVar options:(_ AggOptions)? subagg:(_? '|' _? ElasticsearchAgg)? { return new Command\Elasticsearch\Agg\Metrics\Cardinality($key, $options ? $options[1]:[], $subagg ? $subagg[3]:null); }
    ExtendedStatsAgg = 'extended_stats' _ key:KeyVar options:(_ AggOptions)? subagg:(_? '|' _? ElasticsearchAgg)? { return new Command\Elasticsearch\Agg\Metrics\ExtendedStats($key, $options ? $options[1]:[], $subagg ? $subagg[3]:null); }
    GeoBoundsAgg = 'geo_bound' _ key:KeyVar options:(_ AggOptions)? subagg:(_? '|' _? ElasticsearchAgg)? { return new Command\Elasticsearch\Agg\Metrics\GeoBound($key, $options ? $options[1]:[], $subagg ? $subagg[3]:null); }
    GeoCentroidAgg = 'geo_centroid' _ key:KeyVar options:(_ AggOptions)? subagg:(_? '|' _? ElasticsearchAgg)? { return new Command\Elasticsearch\Agg\Metrics\GeoCentroid($key, $options ? $options[1]:[], $subagg ? $subagg[3]:null); }
    MaxAgg = 'max' _ key:KeyVar options:(_ AggOptions)? subagg:(_? '|' _? ElasticsearchAgg)? { return new Command\Elasticsearch\Agg\Metrics\Max($key, $options ? $options[1]:[], $subagg ? $subagg[3]:null); }
    MinAgg = 'min' _ key:KeyVar options:(_ AggOptions)? subagg:(_? '|' _? ElasticsearchAgg)? { return new Command\Elasticsearch\Agg\Metrics\Min($key, $options ? $options[1]:[], $subagg ? $subagg[3]:null); }
    PercentilesAgg = 'percentiles' _ key:KeyVar options:(_ AggOptions)? subagg:(_? '|' _? ElasticsearchAgg)? { return new Command\Elasticsearch\Agg\Metrics\Percentiles($key, $options ? $options[1]:[], $subagg ? $subagg[3]:null); }
    PercentileRanksAgg = 'percentile_ranks' _ key:KeyVar options:(_ AggOptions)? subagg:(_? '|' _? ElasticsearchAgg)? { return new Command\Elasticsearch\Agg\Metrics\PercentileRanks($key, $options ? $options[1]:[], $subagg ? $subagg[3]:null); }
    StatsAgg = 'stats' _ key:KeyVar options:(_ AggOptions)? subagg:(_? '|' _? ElasticsearchAgg)? { return new Command\Elasticsearch\Agg\Metrics\Stats($key, $options ? $options[1]:[], $subagg ? $subagg[3]:null); }
    SumAgg = 'sum' _ key:KeyVar options:(_ AggOptions)? subagg:(_? '|' _? ElasticsearchAgg)? { return new Command\Elasticsearch\Agg\Metrics\Sum($key, $options ? $options[1]:[], $subagg ? $subagg[3]:null); }
    TopHitsAgg = 'top_hits' _ key:KeyVar options:(_ AggOptions)? subagg:(_? '|' _? ElasticsearchAgg)? { return new Command\Elasticsearch\Agg\Metrics\TopHits($key, $options ? $options[1]:[], $subagg ? $subagg[3]:null); }
    ValueCountAgg = 'value_count' _ key:KeyVar options:(_ AggOptions)? subagg:(_? '|' _? ElasticsearchAgg)? { return new Command\Elasticsearch\Agg\Metrics\ValueCount($key, $options ? $options[1]:[], $subagg ? $subagg[3]:null); }

    DateHistogramAgg = 'date_histogram' _ key:KeyVar options:(_ AggOptions)? subagg:(_? '|' _? ElasticsearchAgg)? { return new Command\Elasticsearch\Agg\Bucket\DateHistogram($key, $options ? $options[1]:[], $subagg ? $subagg[3]:null); }
    DateRangeAgg = 'date_range' _ key:KeyVar options:(_ AggOptions)? subagg:(_? '|' _? ElasticsearchAgg)? { return new Command\Elasticsearch\Agg\Bucket\DateRange($key, $options ? $options[1]:[], $subagg ? $subagg[3]:null); }
    GeoDistanceAgg = 'geo_distance' _ key:KeyVar options:(_ AggOptions)? subagg:(_? '|' _? ElasticsearchAgg)? { return new Command\Elasticsearch\Agg\Bucket\GeoDistance($key, $options ? $options[1]:[], $subagg ? $subagg[3]:null); }
    GeoHashGridAgg = 'geohash_grid' _ key:KeyVar options:(_ AggOptions)? subagg:(_? '|' _? ElasticsearchAgg)? { return new Command\Elasticsearch\Agg\Bucket\GeoHashGrid($key, $options ? $options[1]:[], $subagg ? $subagg[3]:null); }
    HistogramAgg = 'histogram' _ key:KeyVar options:(_ AggOptions)? subagg:(_? '|' _? ElasticsearchAgg)? { return new Command\Elasticsearch\Agg\Bucket\Histogram($key, $options ? $options[1]:[], $subagg ? $subagg[3]:null); }
    IPv4RangeAgg = 'ipv4' _ key:KeyVar options:(_ AggOptions)? subagg:(_? '|' _? ElasticsearchAgg)? { return new Command\Elasticsearch\Agg\Bucket\IPv4($key, $options ? $options[1]:[], $subagg ? $subagg[3]:null); }
    MissingAgg = 'missing' _ key:KeyVar options:(_ AggOptions)? subagg:(_? '|' _? ElasticsearchAgg)? { return new Command\Elasticsearch\Agg\Bucket\Missing($key, $options ? $options[1]:[], $subagg ? $subagg[3]:null); }
    RangeAgg = 'range' _ key:KeyVar options:(_ AggOptions)? subagg:(_? '|' _? ElasticsearchAgg)? { return new Command\Elasticsearch\Agg\Bucket\Range($key, $options ? $options[1]:[], $subagg ? $subagg[3]:null); }
    SignificantTermsAgg = 'significant_terms' _ key:KeyVar options:(_ AggOptions)? subagg:(_? '|' _? ElasticsearchAgg)? { return new Command\Elasticsearch\Agg\Bucket\SignificantTerms($key, $options ? $options[1]:[], $subagg ? $subagg[3]:null); }
    TermsAgg = 'terms' _ key:KeyVar options:(_ AggOptions)? subagg:(_? '|' _? ElasticsearchAgg)? { return new Command\Elasticsearch\Agg\Bucket\Terms($key, $options ? $options[1]:[], $subagg ? $subagg[3]:null); }

Map = 'map' _ first:MapClause rest:(_ MapClause)* { return new Command\Map(Util::combine($first, $rest, 1)); }
  MapClause = MapCombine / MapDelete / MapExpr
    MapCombine = first:KeyVar rest:('+' KeyVar)* '=' target:KeyVar { return [Command\Map::T_COMBINE, Util::combine($first, $rest, 1), $target]; }
    MapDelete = '-' target:KeyVar { return [Command\Map::T_DELETE, $target]; }
    MapExpr = target:KeyVar '=' expr:ExprVar { return [Command\Map::T_EXPR, $target, $expr]; }

Sort = 'sort' _ sort:(SortExpr / SortFields) { return $sort; }
  SortExpr = expr:ExprVar { return new Command\Sort\Expr($expr); }
  SortFields = first:SortClause rest:(_ SortClause)* { return new Command\Sort\Fields(Util::combine($first, $rest, 1)); }
    SortClause = key:KeyVar ',' type:SortType { return [$key, $type]; }
      SortType = SortAsc / SortDesc
        SortAsc = 'asc' { return Command\Sort\Fields::T_ASC; }
        SortDesc = 'desc' { return Command\Sort\Fields::T_DESC; }

Filter = 'filter' _ expr:ExprVar { return new Command\Filter($expr); }

Join = 'join' SEP type:JoinType _ source:Key '=' first:KeyVar rest:('+' rest:KeyVar)* { return new Command\Join($source, Util::combine($first, $rest, 1), $type); }
  JoinType = JoinInner / JoinLeft / JoinRight
    JoinInner = 'inner' { return Command\Join::T_INNER; }
    JoinLeft = 'left' { return Command\Join::T_LEFT; }
    JoinRight = 'right' { return Command\Join::T_RIGHT; }

Load = 'load' _ first:Key rest:(',' Key)* { return new Command\Load(Util::combine($first, $rest, 1)); }

Head = 'head' _ n:IntVar { return new Command\Head($n); }

Tail = 'tail' _ n:IntVar { return new Command\Tail($n); }

Count = 'count' { return new Command\Count; }

Store = '>' _? target:Key { return new Command\Store($target); }


/**
 * Query clauses
 */
ElasticsearchQueryOR = a:ElasticsearchQueryAND b:(_ 'OR' _ ElasticsearchQueryAND)* { return (isset($b) && count($b)) ? ['or' => Util::combine($a, $b, 3)]:$a; }
ElasticsearchQueryAND = a:ElasticsearchQueryNOT b:((_ 'AND')? _ ElasticsearchQueryNOT)* { return (isset($b) && count($b)) ? ['and' => Util::combine($a, $b, 2)]:$a; }
ElasticsearchQueryNOT = a:ElasticsearchQueryUnaryNOT b:(_ 'NOT' _ ElasticsearchQueryUnaryNOT)? { return (isset($b) && count($b)) ? ['and' => [$a, ['not' => ['filter' => $b[3]]]]]:$a; }
ElasticsearchQueryUnaryNOT
  = neg:'-'? '(' _? expr:ElasticsearchQuery _? ')' { return $neg ? ['not' => ['filter' => $expr]]:$expr; }
  / neg:'-'? clause:ElasticsearchQueryClause { return $neg ? ['not' => ['filter' => $clause]]:$clause; }

ElasticsearchQueryClause
  = '_exists_' SEP field:KeyVar { return ['exists' => ['field' => $field]]; }
  / '_missing_' SEP field:KeyVar { return ['missing' => ['field' => $field]]; }
  / field:KeyVar SEP ltype:ElasticsearchQueryRangeLow _? lo:PrimVar _ 'TO' _ hi:PrimVar _? htype:ElasticsearchQueryRangeHigh {
    $ret = [];
    if($lo !== '*') { $ret[$ltype] = $lo; }
    if($hi !== '*') { $ret[$htype] = $hi; }
    return ['range' => [$field => $ret]];
  }
  / field:Key SEP '^' val:ValVar { return ['prefix' => [$field => $val]]; }
  / field:Key SEP '/' regex:RegexVal '/' { return ['regexp' => [$field => $regex]]; }
  / field:Key SEP '(' _? first:Escaped rest:(_ Escaped)* _? ')' { return ['terms' => [$field => Util::combine($first, $rest, 1)]]; }

  / field:Key SEP val:PathVar { return ['terms' => [$field => $val]]; }
  / field:Key SEP val:WCValVar { return ['query' => ['query_string' => ['default_field' => $field, 'query' => $val]]]; }
ElasticsearchQueryRangeLow
  = '[' { return 'lte'; }
  / '{' { return 'lt'; }

ElasticsearchQueryRangeHigh
  = ']' { return 'gte'; }
  / '}' { return 'gt'; }


/**
 * Special values
 */
AggOptions = first:AggOption rest:(_ AggOption)* { return Util::assoc($first, $rest, 1); }
AggOption = field:Key SEP val:PrimVar { return [$field, $val]; }


/**
 * Experimental
 */
/*
  JoinType = JoinUnion / JoinDiff / JoinSub / JoinIntersect
    JoinUnion = 'union' { return Command\Join::T_UNION; }
    JoinDiff = 'diff' { return Command\Join::T_DIFF; }
    JoinSub = 'sub' { return Command\Join::T_SUB; }
    JoinIntersect = 'intersect' { return Command\Join::T_INTERSECT; }
Setting
  = '$' ret:(
    key:'to' SEP val:Date { return [$key, $val]; } /
    key:'from' SEP val:Date { return [$key, $val]; } /
    key:'size' SEP val:Integer { return [$key, $val]; } /
    //key:'sort' SEP '[' val:SortsSetting ']' { return [$key, $val]; } /

    key:'fields' SEP '[' first:Key rest:(_ Key)* ']' { return [$key, Util::combine($first, $rest, 1)]; } /
  ) { return $ret; }

FieldMap
  = field:Key new_field:(SEP Key) { return [$field, $new_field[1]]; }
SortsSetting
  = _? first:FieldSort rest:(_? ',' _? FieldSort)* _? { return Util::combine($first, $rest, 3); }

FieldSort
  = field:Key order:(SEP Order)? { return [$field, isset($order) ? $order[1]:0]; }

Date
  = Integer
  / ('now' / ($[0-9a-zA-Z|/+-]+ '||')) (('+' / '-') Integer ('y' / 'M' / 'w' /'d' / 'h' / 'm' / 's'))* // FIXME
*/
