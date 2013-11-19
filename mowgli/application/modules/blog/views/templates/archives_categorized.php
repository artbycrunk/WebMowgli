

<ul>
        {archives}
                <li><a href="{year:url}">{year} [{year:count}]</a>

                        <ul>
                                {months}
                                        <li><a href="{month:url}">{month} ({month:count})</a></li>
                                {/months}
                        </ul>

                </li>
        {/archives}

</ul>
