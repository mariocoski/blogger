<?php

namespace App\Models;

use App\Services\ParsedownService;
use Auth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Article extends Model
{

    use Searchable;

    public function searchableAs()
    {
        return 'articles_index';
    }

    public function toSearchableArray()
    {
        if ($this->is_published === 1 && $this->published_at < Carbon::now()) {
            return $this->toArray();
        }

        return [];
    }

    public function fans()
    {
        return $this->belongsToMany(User::class, 'favourites');
    }

    public function isFavourite()
    {
        return $this->fans()->get()->contains(Auth::user()->id);
    }

    protected $guarded = [];

    protected $dates = ['published_at'];

    protected $append = ['author_name'];

    public function category()
    {
        return $this->belongsTo('App\Models\Category');
    }

    public function tags()
    {
        return $this->belongsToMany('App\Models\Tag', 'articles_tags', 'article_id', 'tag_id');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', 1)->whereNotNull("published_at")->where('published_at', '<', Carbon::now());
    }

    public function updateTags($commaDelimittedTags)
    {
        $tags = explode(',', $commaDelimittedTags);
        $this->tags()->sync($tags);
    }

    public function setContentAttribute($value)
    {
        $this->attributes['content'] = $value;
        $this->attributes['html_content'] = ParsedownService::toHTML($value);
    }

    public function getAssociatedTagsAttribute()
    {
        return $this->tags->map(function ($tag) {
            return $tag->id;
        })->implode(',');
    }
    public function author()
    {
        return $this->belongsTo('App\Models\User', 'author_id');
    }

    public function setPublishedAtAttribute($value)
    {
        $this->attributes['published_at'] = Carbon::parse($value)->format('Y-m-d H:i:00');
    }

    public function getAuthorNameAttribute()
    {
        return ($this->author->display_name) ?? $this->author->email;
    }

    public function getCategoryNameAttribute()
    {
        return $this->category->name;
    }

}
