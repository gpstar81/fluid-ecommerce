"function" != typeof Object.create &&
    (Object.create = function (t) {
        function e() {}
        return (e.prototype = t), new e();
    }),
    (function (t, e, a, i) {
        t.fn.socialfeed = function (e) {
            function a() {
                o.callback && o.callback();
            }
            function s(t, e) {
                (this.content = e),
                    (this.content.social_network = t),
                    (this.content.icon = e.icon),
                    (this.content.icon_style = e.icon_style),
                    (this.content.attachment = this.content.attachment === i ? "" : this.content.attachment),
                    (this.content.time_ago = e.dt_create.fromNow()),
                    (this.content.date = e.dt_create.format(o.date_format)),
                    (this.content.dt_create = this.content.dt_create.valueOf()),
                    (this.content.text = m.wrapLinks(m.shorten(e.message + " " + e.description), e.social_network)),
                    (this.content.moderation_passed = !o.moderation || o.moderation(this.content)),
                    p[t].posts.push(this);
            }
            var n = { plugin_folder: "", template: "template.html", show_media: !1, media_min_width: 300, length: 500, date_format: "ll" },
                o = t.extend(n, e),
                r = t(this),
                c = ["facebook", "instagram", "vk", "google", "blogspot", "twitter", "pinterest", "rss", "youtube"],
                u = 0,
                l = 0;
            c.forEach(function (t) {
                o[t] && (o[t].accounts ? (u += o[t].limit * o[t].accounts.length) : (u += o[t].limit));
            });
            var m = {
                request: function (e, a) {
                    t.ajax({ url: e, dataType: "jsonp", success: a });
                },
                get_request: function (e, a) {
                    t.get(e, a, "json");
                },
                wrapLinks: function (t, e) {
                    var a = /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/gi;
                    return (t = "google-plus" === e ? t.replace(/(@|#)([a-z0-9_]+['])/gi, m.wrapGoogleplusTagTemplate) : t.replace(a, m.wrapLinkTemplate));
                },
                wrapLinkTemplate: function (t) {
                    return '<a target="_blank" href="' + t + '">' + t + "</a>";
                },
                wrapGoogleplusTagTemplate: function (t) {
                    return '<a target="_blank" href="https://plus.google.com/s/' + t + '" >' + t + "</a>";
                },
                shorten: function (e) {
                    return (e = t.trim(e)), e.length > o.length ? jQuery.trim(e).substring(0, o.length).split(" ").slice(0, -1).join(" ") + "..." : e;
                },
                stripHTML: function (t) {
                    return void 0 === t || null === t ? "" : t.replace(/(<([^>]+)>)|nbsp;|\s{2,}|/gi, "");
                },
            };
            s.prototype = {
                render: function () {
                    var e = p.template(this.content),
                        i = this.content;
                    if ((t("#f-loading-overlay-social-media").hide(), 0 == appendNumber)) js_social_append(i.dt_create), t("#social-media-" + appendNumber).append(e);
                    else {
                        var s = 0,
                            n = -1;
                        if (
                            (t.each(t(r).children(), function () {
                                if (t(this).attr("dt-create") < i.dt_create) return (n = s), !1;
                                s++;
                            }),
                            js_social_append(i.dt_create),
                            t("#social-media-" + appendNumber).append(e),
                            n >= 0)
                        ) {
                            n++;
                            var c = t(r).children("div:nth-child(" + n + ")"),
                                m = t(r).children("div:last-child");
                            t(m).insertBefore(c);
                        }
                    }
                    if (o.media_min_width) {
                        var d = "[social-feed-id=" + i.id + "] img.attachment",
                            h = t(d),
                            g = new Image(),
                            _ = h.attr("src");
                        t(g)
                            .on("load", function () {
                                g.width < o.media_min_width && h.hide(), delete g;
                            })
                            .on("error", function () {
                                h.hide();
                            })
                            .attr({ src: _ });
                    }
                    ++l == u && a();
                },
            };
            var p = {
                template: !1,
                init: function () {
                    p.getTemplate(function () {
                        c.forEach(function (t) {
                            o[t] &&
                                (o[t].accounts
                                    ? o[t].accounts.forEach(function (e) {
                                          p[t].getData(e);
                                      })
                                    : o[t].urls
                                    ? o[t].urls.forEach(function (e) {
                                          p[t].getData(e);
                                      })
                                    : p[t].getData());
                        });
                    });
                },
                getTemplate: function (e) {
                    return p.template
                        ? e()
                        : o.template_html
                        ? ((p.template = doT.template(o.template_html)), e())
                        : void t.get(o.template, function (t) {
                              return (p.template = doT.template(t)), e();
                          });
                },
                twitter: {
                    posts: [],
                    loaded: !1,
                    api: "https://api.tweecool.com/",
                    getData: function (t) {
                        var e = new Codebird();
                        switch ((e.setConsumerKey(o.twitter.consumer_key, o.twitter.consumer_secret), o.twitter.proxy !== i && e.setProxy(o.twitter.proxy), t[0])) {
                            case "@":
                                var a = t.substr(1);
                                e.__call("statuses_userTimeline", "id=" + a + "&count=" + o.twitter.limit, p.twitter.utility.getPosts, !0);
                                break;
                            case "#":
                                var s = t.substr(1);
                                e.__call(
                                    "search_tweets",
                                    "q=" + s + "&count=" + o.twitter.limit,
                                    function (t) {
                                        p.twitter.utility.getPosts(t.statuses);
                                    },
                                    !0
                                );
                        }
                    },
                    utility: {
                        getPosts: function (e) {
                            e &&
                                t.each(e, function () {
                                    var t = this;
                                    new s("twitter", p.twitter.utility.unifyPostData(t)).render();
                                });
                        },
                        unifyPostData: function (t) {
                            var e = {};
                            if (
                                t.id &&
                                ((e.id = t.id),
                                (e.dt_create = moment(new Date(t.created_at))),
                                (e.author_link = "http://twitter.com/" + t.user.screen_name),
                                (e.author_picture = t.user.profile_image_url_https),
                                (e.post_url = e.author_link + "/status/" + t.id_str),
                                (e.author_name = t.user.name),
                                (e.message = t.text),
                                (e.description = ""),
                                (e.icon = "twitter-square"),
                                (e.icon_style = "color: #2EAEF7;"),
                                (e.link = "http://twitter.com/" + t.user.screen_name + "/status/" + t.id_str),
                                !0 === o.show_media && t.entities.media && t.entities.media.length > 0)
                            ) {
                                var a = t.entities.media[0].media_url_https;
                                a && (e.attachment = '<img class="attachment" src="' + a + '" />');
                            }
                            return e;
                        },
                    },
                },
                facebook: {
                    posts: [],
                    graph: "https://graph.facebook.com/",
                    loaded: !1,
                    getData: function (t) {
                        var e = function (t) {
                                m.request(t, p.facebook.utility.getPosts);
                            },
                            a = "?fields=id,from,name,message,created_time,story,description,link";
                        a += !0 === o.show_media ? ",picture,object_id" : "";
                        var i,
                            s = "&limit=" + o.facebook.limit,
                            n = "&access_token=" + o.facebook.access_token + "&callback=?";
                        switch (t[0]) {
                            case "@":
                                var r = t.substr(1);
                                p.facebook.utility.getUserId(r, function (t) {
                                    "" !== t.id && ((i = p.facebook.graph + "v2.4/" + t.id + "/posts" + a + s + n), e(i));
                                });
                                break;
                            case "!":
                                var c = t.substr(1);
                                (i = p.facebook.graph + "v2.4/" + c + "/feed" + a + s + n), e(i);
                                break;
                            default:
                                e(i);
                        }
                    },
                    utility: {
                        getUserId: function (e, a) {
                            var i = "https://graph.facebook.com/" + e + "?" + ("&access_token=" + o.facebook.access_token + "&callback=?");
                            t.get(i, a, "json");
                        },
                        prepareAttachment: function (t) {
                            var e = t.picture;
                            return (
                                -1 !== e.indexOf("_b.") ||
                                    (-1 !== e.indexOf("safe_image.php")
                                        ? (e = p.facebook.utility.getExternalImageURL(e, "url"))
                                        : -1 !== e.indexOf("app_full_proxy.php")
                                        ? (e = p.facebook.utility.getExternalImageURL(e, "src"))
                                        : t.object_id && (e = p.facebook.graph + t.object_id + "/picture/?type=normal")),
                                '<img class="attachment" src="' + e + '" />'
                            );
                        },
                        getExternalImageURL: function (t, e) {
                            return (t = decodeURIComponent(t).split(e + "=")[1]), -1 === t.indexOf("fbcdn-sphotos") ? t.split("&")[0] : t;
                        },
                        getPosts: function (t) {
                            t.data &&
                                t.data.forEach(function (t) {
                                    new s("facebook", p.facebook.utility.unifyPostData(t)).render();
                                });
                        },
                        unifyPostData: function (t) {
                            var e = {},
                                a = t.message ? t.message : t.story;
                            if (
                                ((e.id = t.id),
                                (e.dt_create = moment(t.created_time)),
                                (e.author_link = "http://facebook.com/" + t.from.id),
                                (e.author_picture = p.facebook.graph + t.from.id + "/picture"),
                                (e.author_name = t.from.name),
                                (e.name = t.name || ""),
                                (e.message = a || ""),
                                (e.icon = "facebook-official"),
                                (e.icon_style = "color: #3B5998;"),
                                (e.description = t.description ? t.description : ""),
                                (e.link = t.link ? t.link : "http://facebook.com/" + t.from.id),
                                !0 === o.show_media && t.picture)
                            ) {
                                var i = p.facebook.utility.prepareAttachment(t);
                                i && ((e.attachment = i), (e.attachment = e.attachment.replace("http://", "https://")));
                            }
                            return e;
                        },
                    },
                },
                google: {
                    posts: [],
                    loaded: !1,
                    api: "https://www.googleapis.com/plus/v1/",
                    getData: function (t) {
                        var e;
                        switch (t[0]) {
                            case "#":
                                var a = t.substr(1);
                                (e = p.google.api + "activities?query=" + a + "&key=" + o.google.access_token + "&maxResults=" + o.google.limit), m.get_request(e, p.google.utility.getPosts);
                                break;
                            case "@":
                                var i = t.substr(1);
                                (e = p.google.api + "people/" + i + "/activities/public?key=" + o.google.access_token + "&maxResults=" + o.google.limit), m.get_request(e, p.google.utility.getPosts);
                        }
                    },
                    utility: {
                        getPosts: function (e) {
                            e.items &&
                                t.each(e.items, function (t) {
                                    new s("google", p.google.utility.unifyPostData(e.items[t])).render();
                                });
                        },
                        unifyPostData: function (e) {
                            var a = {};
                            return (
                                (a.id = e.id),
                                (a.attachment = ""),
                                (a.description = ""),
                                (a.dt_create = moment(e.published)),
                                (a.author_link = e.actor.url),
                                (a.author_picture = e.actor.image.url),
                                (a.author_name = e.actor.displayName),
                                (a.icon = "google-plus-square"),
                                (a.icon_style = "color: #CE3525;"),
                                !0 === o.show_media &&
                                    e.object.attachments &&
                                    t.each(e.object.attachments, function () {
                                        var t = "";
                                        this.fullImage ? (t = this.fullImage.url) : "album" === this.objectType && this.thumbnails && this.thumbnails.length > 0 && this.thumbnails[0].image && (t = this.thumbnails[0].image.url),
                                            (a.attachment = '<img class="attachment" src="' + t + '"/>');
                                    }),
                                (a.message = e.title),
                                (a.link = e.url),
                                a
                            );
                        },
                    },
                },
                instagram: {
                    posts: [],
                    api: "https://api.instagram.com/v1/",
                    loaded: !1,
                    accessType: function () {
                        return void 0 === o.instagram.access_token && void 0 === o.instagram.client_id
                            ? (console.log("You need to define a client_id or access_token to authenticate with Instagram's API."), i)
                            : (o.instagram.access_token && (o.instagram.client_id = i), (o.instagram.access_type = void 0 === o.instagram.client_id ? "access_token" : "client_id"), o.instagram.access_type);
                    },
                    getData: function (t) {
                        var e;
                        if ("undefined" !== this.accessType()) var a = o.instagram.access_type + "=" + o.instagram[o.instagram.access_type];
                        switch (t[0]) {
                            case "@":
                                var i = t.substr(1);
                                (e = p.instagram.api + "users/search/?q=" + i + "&" + a + "&count=1&callback=?"), m.request(e, p.instagram.utility.getUsers);
                                break;
                            case "#":
                                var s = t.substr(1);
                                (e = p.instagram.api + "tags/" + s + "/media/recent/?" + a + "&count=" + o.instagram.limit + "&callback=?"), m.request(e, p.instagram.utility.getImages);
                                break;
                            case "&":
                                var n = t.substr(1);
                                (e = p.instagram.api + "users/" + n + "/?" + a + "&count=" + o.instagram.limit + "&callback=?"), m.request(e, p.instagram.utility.getUsers);
                        }
                    },
                    utility: {
                        getImages: function (t) {
                            t.data &&
                                t.data.forEach(function (t) {
                                    new s("instagram", p.instagram.utility.unifyPostData(t)).render();
                                });
                        },
                        getUsers: function (t) {
                            console.log(t);
                            if ("undefined" !== o.instagram.access_type) var e = o.instagram.access_type + "=" + o.instagram[o.instagram.access_type];
                            jQuery.isArray(t.data) || (t.data = [t.data]),
                                t.data.forEach(function (t) {
                                    var a = p.instagram.api + "users/" + t.id + "/media/recent/?" + e + "&count=" + o.instagram.limit + "&callback=?";
                                    m.request(a, p.instagram.utility.getImages);
                                });
                        },
                        unifyPostData: function (t) {
                            var e = {};
                            return (
                                (e.id = t.id),
                                (e.dt_create = moment(1e3 * t.created_time)),
                                (e.author_link = "http://instagram.com/" + t.user.username),
                                (e.author_picture = t.user.profile_picture),
                                (e.author_name = t.user.full_name || t.user.username),
                                (e.message = t.caption && t.caption ? t.caption.text : ""),
                                (e.description = ""),
                                (e.icon = "instagram"),
                                (e.icon_style = "color: black;"),
                                (e.link = t.link),
                                null != t.videos
                                    ? (e.attachment =
                                          '<div class="video-container"><video controls preload="none" width="100%" poster="' +
                                          t.images.standard_resolution.url +
                                          '"><source src="' +
                                          t.videos.standard_resolution.url +
                                          '">Your browser does not support html5 video</video></div>')
                                    : o.show_media && (e.attachment = '<img class="attachment" src="' + t.images.standard_resolution.url + '" />'),
                                e
                            );
                        },
                    },
                },
                vk: {
                    posts: [],
                    loaded: !1,
                    base: "http://vk.com/",
                    api: "https://api.vk.com/method/",
                    user_json_template: "https://api.vk.com/method/users.get?fields=first_name,%20last_name,%20screen_name,%20photo&uid=",
                    group_json_template: "https://api.vk.com/method/groups.getById?fields=first_name,%20last_name,%20screen_name,%20photo&gid=",
                    getData: function (t) {
                        var e;
                        switch (t[0]) {
                            case "@":
                                var a = t.substr(1);
                                (e = p.vk.api + "wall.get?owner_id=" + a + "&filter=" + o.vk.source + "&count=" + o.vk.limit + "&callback=?"), m.get_request(e, p.vk.utility.getPosts);
                                break;
                            case "#":
                                var i = t.substr(1);
                                (e = p.vk.api + "newsfeed.search?q=" + i + "&count=" + o.vk.limit + "&callback=?"), m.get_request(e, p.vk.utility.getPosts);
                        }
                    },
                    utility: {
                        getPosts: function (e) {
                            e.response &&
                                t.each(e.response, function () {
                                    if (this != parseInt(this) && "post" === this.post_type) {
                                        var t = this.owner_id ? this.owner_id : this.from_id,
                                            a = t > 0 ? p.vk.user_json_template + t + "&callback=?" : p.vk.group_json_template + -1 * t + "&callback=?",
                                            i = this;
                                        m.get_request(a, function (t) {
                                            p.vk.utility.unifyPostData(t, i, e);
                                        });
                                    }
                                });
                        },
                        unifyPostData: function (t, e, a) {
                            var i = {};
                            if (
                                ((i.id = e.id),
                                (i.dt_create = moment.unix(e.date)),
                                (i.description = " "),
                                (i.message = m.stripHTML(e.text)),
                                (i.icon = "vk"),
                                (i.icon_style = "color: #4C75A3"),
                                o.show_media &&
                                    e.attachment &&
                                    ("link" === e.attachment.type && (i.attachment = '<img class="attachment" src="' + e.attachment.link.image_src + '" />'),
                                    "video" === e.attachment.type && (i.attachment = '<img class="attachment" src="' + e.attachment.video.image_big + '" />'),
                                    "photo" === e.attachment.type && (i.attachment = '<img class="attachment" src="' + e.attachment.photo.src_big + '" />')),
                                e.from_id > 0)
                            ) {
                                var n = p.vk.user_json_template + e.from_id + "&callback=?";
                                m.get_request(n, function (t) {
                                    new s("vk", p.vk.utility.getUser(t, i, e, a)).render();
                                });
                            } else {
                                var r = p.vk.group_json_template + -1 * e.from_id + "&callback=?";
                                m.get_request(r, function (t) {
                                    new s("vk", p.vk.utility.getGroup(t, i, e, a)).render();
                                });
                            }
                        },
                        getUser: function (t, e, a, i) {
                            return (
                                (e.author_name = t.response[0].first_name + " " + t.response[0].last_name),
                                (e.author_picture = t.response[0].photo),
                                (e.author_link = p.vk.base + t.response[0].screen_name),
                                (e.link = p.vk.base + t.response[0].screen_name + "?w=wall" + a.from_id + "_" + a.id),
                                e
                            );
                        },
                        getGroup: function (t, e, a, i) {
                            return (
                                (e.author_name = t.response[0].name),
                                (e.author_picture = t.response[0].photo),
                                (e.author_link = p.vk.base + t.response[0].screen_name),
                                (e.link = p.vk.base + t.response[0].screen_name + "?w=wall-" + t.response[0].gid + "_" + a.id),
                                e
                            );
                        },
                    },
                },
                blogspot: {
                    loaded: !1,
                    getData: function (t) {
                        var e;
                        switch (t[0]) {
                            case "@":
                                (e = "http://" + t.substr(1) + ".blogspot.com/feeds/posts/default?alt=json-in-script&callback=?"), request(e, getPosts);
                        }
                    },
                    utility: {
                        getPosts: function (e) {
                            t.each(e.feed.entry, function () {
                                var t = {},
                                    e = this;
                                (t.id = e.id.$t.replace(/[^a-z0-9]/gi, "")),
                                    (t.dt_create = moment(e.published.$t)),
                                    (t.author_link = e.author[0].uri.$t),
                                    (t.author_picture = "http:" + e.author[0].gd$image.src),
                                    (t.author_name = e.author[0].name.$t),
                                    (t.message = e.title.$t + "</br></br>" + stripHTML(e.content.$t)),
                                    (t.description = ""),
                                    (t.link = e.link.pop().href),
                                    (t.icon = "blogspot"),
                                    (t.icon_style = "color: #F48120;"),
                                    o.show_media && e.media$thumbnail && (t.attachment = '<img class="attachment" src="' + e.media$thumbnail.url + '" />'),
                                    t.render();
                            });
                        },
                    },
                },
                pinterest: {
                    posts: [],
                    loaded: !1,
                    apiv1: "https://api.pinterest.com/v1/",
                    getData: function (t) {
                        var e,
                            a = "limit=" + o.pinterest.limit,
                            i = "fields=id,created_at,link,note,creator(url,first_name,last_name,image),image&access_token=" + o.pinterest.access_token + "&" + a + "&callback=?";
                        switch (t[0]) {
                            case "@":
                                var s = t.substr(1);
                                e = "me" === s ? p.pinterest.apiv1 + "me/pins/?" + i : p.pinterest.apiv1 + "boards/" + s + "/pins?" + i;
                        }
                        m.request(e, p.pinterest.utility.getPosts);
                    },
                    utility: {
                        getPosts: function (t) {
                            t.data.forEach(function (t) {
                                new s("pinterest", p.pinterest.utility.unifyPostData(t)).render();
                            });
                        },
                        unifyPostData: function (t) {
                            var e = {};
                            return (
                                (e.id = t.id),
                                (e.dt_create = moment(t.created_at)),
                                (e.author_link = t.creator.url),
                                (e.author_picture = t.creator.image["60x60"].url),
                                (e.author_name = t.creator.first_name + t.creator.last_name),
                                (e.message = t.note),
                                (e.description = ""),
                                (e.social_network = "pinterest"),
                                (e.icon = "pinterest-square"),
                                (e.icon_style = "color: #CB2027;"),
                                (e.link = t.link ? t.link : "https://www.pinterest.com/pin/" + t.id),
                                o.show_media && (e.attachment = '<img class="attachment" src="' + t.image.original.url + '" />'),
                                e
                            );
                        },
                    },
                },
                rss: {
                    posts: [],
                    loaded: !1,
                    api: "https://ajax.googleapis.com/ajax/services/feed/load?v=1.0",
                    getData: function (t) {
                        var e = "&num=" + o.rss.limit,
                            a = p.rss.api + e + "&q=" + encodeURIComponent(t);
                        m.request(a, p.rss.utility.getPosts);
                    },
                    utility: {
                        getPosts: function (e) {
                            t.each(e.responseData.feed.entries, function (t, e) {
                                new s("rss", p.rss.utility.unifyPostData(t, e)).render();
                            });
                        },
                        unifyPostData: function (t, e) {
                            var a = {};
                            return (
                                (a.id = t),
                                (a.dt_create = moment(e.publishedDate, "ddd, DD MMM YYYY HH:mm:ss ZZ", "en")),
                                (a.author_link = ""),
                                (a.author_picture = ""),
                                (a.author_name = e.author),
                                (a.message = m.stripHTML(e.title)),
                                (a.description = m.stripHTML(e.content)),
                                (a.social_network = "rss"),
                                (a.icon = "rss"),
                                (a.icon_style = "color: black;"),
                                (a.link = e.link),
                                o.show_media && e.mediaGroups && (a.attachment = '<img class="attachment" src="' + e.mediaGroups[0].contents[0].url + '" />'),
                                a
                            );
                        },
                    },
                },
                youtube: {
                    posts: [],
                    loaded: !1,
                    getData: function (t) {
                        var e = "?num=" + o.youtube.limit,
                            a = o.youtube.feed_url + e;
                        m.request(a, p.youtube.utility.getPosts);
                    },
                    utility: {
                        getPosts: function (e) {
                            t.each(e.items.entry, function (t, e) {
                                new s("youtube", p.youtube.utility.unifyPostData(t, e)).render();
                            });
                        },
                        unifyPostData: function (t, e) {
                            var a = {};
                            (a.id = t),
                                (a.dt_create = moment(e.published)),
                                (a.author_link = e.author.uri),
                                (a.author_picture = o.youtube.picture),
                                (a.author_name = e.author.name),
                                (a.message = m.stripHTML(e.title)),
                                (a.description = m.stripHTML(e.title)),
                                (a.social_network = "youtube"),
                                (a.icon = "youtube-square"),
                                (a.icon_style = "color: #FF0000;"),
                                (a.link = e.link["@attributes"].href);
                            var i = e.id.replace("yt:video:", "");
                            return (a.attachment = '<div class="video-container"><iframe type="text/html" width="560" height="349" src="https://www.youtube.com/embed/' + i + '" frameborder="0" allowfullscreen></iframe></div>'), a;
                        },
                    },
                },
            };
            return this.each(function () {
                p.init(),
                    o.update_period &&
                        setInterval(function () {
                            return p.init();
                        }, o.update_period);
            });
        };
    })(jQuery);
