        <div style="background-color: #9E9BD6">
                {images}<br/>
                        {image:id}<br/>
                        {image:name}<br/>
                        {image:name_url}<br/>
                        {image:description}<br/>
                        <img src="{site:root}/static/modules/gallery/uploads/images{image:uri_thumb}" alt="no image"/>
                {/images}
        </div>

<br/><br/>      
{categories}

<br/>NEW CATEGORY<br/>

<div style="background-color: #CEC6C6">
        {categ:id}<br/>
        {categ:name}<br/>
        {categ:name_url}<br/>
        {categ:description}<br/><br/><br/>

        <div style="background-color: #9E9AD6">
                {categ:images}<br/>
                        {image:id}<br/>
                        {image:name}<br/>
                        {image:name_url}<br/>
                        {image:description}<br/>
                        <a href="{image:uri}"><img src="{image:uri_thumb}" alt="no image"/></a>
                {/categ:images}
        </div>
</div>

{/categories}