this.wc=this.wc||{},this.wc.blocks=this.wc.blocks||{},this.wc.blocks["product-tag"]=function(t){function e(e){for(var r,i,u=e[0],s=e[1],a=e[2],b=0,d=[];b<u.length;b++)i=u[b],Object.prototype.hasOwnProperty.call(o,i)&&o[i]&&d.push(o[i][0]),o[i]=0;for(r in s)Object.prototype.hasOwnProperty.call(s,r)&&(t[r]=s[r]);for(l&&l(e);d.length;)d.shift()();return c.push.apply(c,a||[]),n()}function n(){for(var t,e=0;e<c.length;e++){for(var n=c[e],r=!0,u=1;u<n.length;u++){var s=n[u];0!==o[s]&&(r=!1)}r&&(c.splice(e--,1),t=i(i.s=n[0]))}return t}var r={},o={32:0},c=[];function i(e){if(r[e])return r[e].exports;var n=r[e]={i:e,l:!1,exports:{}};return t[e].call(n.exports,n,n.exports,i),n.l=!0,n.exports}i.m=t,i.c=r,i.d=function(t,e,n){i.o(t,e)||Object.defineProperty(t,e,{enumerable:!0,get:n})},i.r=function(t){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})},i.t=function(t,e){if(1&e&&(t=i(t)),8&e)return t;if(4&e&&"object"==typeof t&&t&&t.__esModule)return t;var n=Object.create(null);if(i.r(n),Object.defineProperty(n,"default",{enumerable:!0,value:t}),2&e&&"string"!=typeof t)for(var r in t)i.d(n,r,function(e){return t[e]}.bind(null,r));return n},i.n=function(t){var e=t&&t.__esModule?function(){return t.default}:function(){return t};return i.d(e,"a",e),e},i.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},i.p="";var u=window.webpackWcBlocksJsonp=window.webpackWcBlocksJsonp||[],s=u.push.bind(u);u.push=e,u=u.slice();for(var a=0;a<u.length;a++)e(u[a]);var l=s;return c.push([828,0]),n()}({0:function(t,e){!function(){t.exports=this.wp.element}()},1:function(t,e){!function(){t.exports=this.wp.i18n}()},106:function(t,e){},107:function(t,e){},108:function(t,e){},109:function(t,e){},110:function(t,e){},111:function(t,e){},112:function(t,e){},113:function(t,e){},114:function(t,e){},115:function(t,e){},116:function(t,e){},117:function(t,e){},118:function(t,e){},124:function(t,e,n){"use strict";var r=n(0),o=n(1),c=n(4);n(2);e.a=function(t){var e=t.value,n=t.setAttributes;return Object(r.createElement)(c.SelectControl,{label:Object(o.__)("Order products by","woo-gutenberg-products-block"),value:e,options:[{label:Object(o.__)("Newness - newest first","woo-gutenberg-products-block"),value:"date"},{label:Object(o.__)("Price - low to high","woo-gutenberg-products-block"),value:"price_asc"},{label:Object(o.__)("Price - high to low","woo-gutenberg-products-block"),value:"price_desc"},{label:Object(o.__)("Rating - highest first","woo-gutenberg-products-block"),value:"rating"},{label:Object(o.__)("Sales - most first","woo-gutenberg-products-block"),value:"popularity"},{label:Object(o.__)("Title - alphabetical","woo-gutenberg-products-block"),value:"title"},{label:Object(o.__)("Menu Order","woo-gutenberg-products-block"),value:"menu_order"}],onChange:function(t){return n({orderby:t})}})}},13:function(t,e){!function(){t.exports=this.regeneratorRuntime}()},165:function(t,e,n){"use strict";n.d(e,"a",(function(){return c}));var r=n(0),o=n(6),c=Object(r.createElement)("img",{src:o.S+"img/grid.svg",alt:"Grid Preview",width:"230",height:"250",style:{width:"100%"}})},19:function(t,e){!function(){t.exports=this.wp.apiFetch}()},20:function(t,e){!function(){t.exports=this.wp.url}()},21:function(t,e){!function(){t.exports=this.wp.data}()},22:function(t,e){!function(){t.exports=this.wp.blockEditor}()},23:function(t,e){!function(){t.exports=this.wp.blocks}()},26:function(t,e){!function(){t.exports=this.moment}()},28:function(t,e){!function(){t.exports=this.wp.htmlEntities}()},3:function(t,e){!function(){t.exports=this.wc.wcSettings}()},31:function(t,e){!function(){t.exports=this.wp.primitives}()},34:function(t,e){!function(){t.exports=this.wp.dataControls}()},36:function(t,e,n){"use strict";n.d(e,"h",(function(){return d})),n.d(e,"e",(function(){return g})),n.d(e,"b",(function(){return p})),n.d(e,"i",(function(){return f})),n.d(e,"f",(function(){return h})),n.d(e,"c",(function(){return O})),n.d(e,"d",(function(){return j})),n.d(e,"g",(function(){return w})),n.d(e,"a",(function(){return m}));var r=n(5),o=n.n(r),c=n(20),i=n(19),u=n.n(i),s=n(7),a=n(6);function l(t,e){var n=Object.keys(t);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(t);e&&(r=r.filter((function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable}))),n.push.apply(n,r)}return n}function b(t){for(var e=1;e<arguments.length;e++){var n=null!=arguments[e]?arguments[e]:{};e%2?l(Object(n),!0).forEach((function(e){o()(t,e,n[e])})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(n)):l(Object(n)).forEach((function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(n,e))}))}return t}var d=function(t){var e=t.selected,n=void 0===e?[]:e,r=t.search,o=void 0===r?"":r,i=t.queryArgs,l=function(t){var e=t.selected,n=void 0===e?[]:e,r=t.search,o=void 0===r?"":r,i=t.queryArgs,u=void 0===i?[]:i,s={per_page:a.u?100:0,catalog_visibility:"any",search:o,orderby:"title",order:"asc"},l=[Object(c.addQueryArgs)("/wc/store/products",b(b({},s),u))];return a.u&&n.length&&l.push(Object(c.addQueryArgs)("/wc/store/products",{catalog_visibility:"any",include:n})),l}({selected:n,search:o,queryArgs:void 0===i?[]:i});return Promise.all(l.map((function(t){return u()({path:t})}))).then((function(t){return Object(s.uniqBy)(Object(s.flatten)(t),"id").map((function(t){return b(b({},t),{},{parent:0})}))})).catch((function(t){throw t}))},g=function(t){return u()({path:"/wc/store/products/".concat(t)})},p=function(){return u()({path:"wc/store/products/attributes"})},f=function(t){return u()({path:"wc/store/products/attributes/".concat(t,"/terms")})},h=function(t){var e=t.selected,n=function(t){var e=t.selected,n=void 0===e?[]:e,r=t.search,o=[Object(c.addQueryArgs)("wc/store/products/tags",{per_page:a.w?100:0,orderby:a.w?"count":"name",order:a.w?"desc":"asc",search:r})];return a.w&&n.length&&o.push(Object(c.addQueryArgs)("wc/store/products/tags",{include:n})),o}({selected:void 0===e?[]:e,search:t.search});return Promise.all(n.map((function(t){return u()({path:t})}))).then((function(t){return Object(s.uniqBy)(Object(s.flatten)(t),"id")}))},O=function(t){return u()({path:Object(c.addQueryArgs)("wc/store/products/categories",b({per_page:0},t))})},j=function(t){return u()({path:"wc/store/products/categories/".concat(t)})},w=function(t){return u()({path:Object(c.addQueryArgs)("wc/store/products",{per_page:0,type:"variation",parent:t})})},m=function(t,e){if(!t.title.raw)return t.slug;var n=1===e.filter((function(e){return e.title.raw===t.title.raw})).length;return t.title.raw+(n?"":" - ".concat(t.slug))}},4:function(t,e){!function(){t.exports=this.wp.components}()},48:function(t,e){!function(){t.exports=this.wp.keycodes}()},54:function(t,e,n){"use strict";var r=n(5),o=n.n(r),c=n(24),i=n.n(c),u=n(9);n(2);function s(t,e){var n=Object.keys(t);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(t);e&&(r=r.filter((function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable}))),n.push.apply(n,r)}return n}e.a=function(t){var e=t.srcElement,n=t.size,r=void 0===n?24:n,c=i()(t,["srcElement","size"]);return Object(u.isValidElement)(e)&&Object(u.cloneElement)(e,function(t){for(var e=1;e<arguments.length;e++){var n=null!=arguments[e]?arguments[e]:{};e%2?s(Object(n),!0).forEach((function(e){o()(t,e,n[e])})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(n)):s(Object(n)).forEach((function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(n,e))}))}return t}({width:r,height:r},c))}},58:function(t,e){!function(){t.exports=this.wp.hooks}()},6:function(t,e,n){"use strict";n.d(e,"l",(function(){return o})),n.d(e,"I",(function(){return c})),n.d(e,"O",(function(){return i})),n.d(e,"y",(function(){return u})),n.d(e,"A",(function(){return s})),n.d(e,"m",(function(){return a})),n.d(e,"z",(function(){return l})),n.d(e,"C",(function(){return b})),n.d(e,"o",(function(){return d})),n.d(e,"B",(function(){return g})),n.d(e,"n",(function(){return p})),n.d(e,"E",(function(){return f})),n.d(e,"u",(function(){return h})),n.d(e,"w",(function(){return O})),n.d(e,"r",(function(){return j})),n.d(e,"s",(function(){return w})),n.d(e,"t",(function(){return m})),n.d(e,"k",(function(){return y})),n.d(e,"K",(function(){return v})),n.d(e,"P",(function(){return k})),n.d(e,"q",(function(){return _})),n.d(e,"p",(function(){return S})),n.d(e,"H",(function(){return E})),n.d(e,"c",(function(){return P})),n.d(e,"v",(function(){return C})),n.d(e,"S",(function(){return A})),n.d(e,"T",(function(){return T})),n.d(e,"J",(function(){return D})),n.d(e,"a",(function(){return B})),n.d(e,"M",(function(){return R})),n.d(e,"b",(function(){return N})),n.d(e,"L",(function(){return M})),n.d(e,"D",(function(){return I})),n.d(e,"i",(function(){return V})),n.d(e,"N",(function(){return L})),n.d(e,"h",(function(){return G})),n.d(e,"j",(function(){return Q})),n.d(e,"G",(function(){return q})),n.d(e,"F",(function(){return F})),n.d(e,"R",(function(){return U})),n.d(e,"Q",(function(){return W})),n.d(e,"d",(function(){return $})),n.d(e,"e",(function(){return J})),n.d(e,"f",(function(){return K})),n.d(e,"g",(function(){return X})),n.d(e,"x",(function(){return Y})),n.d(e,"W",(function(){return tt})),n.d(e,"X",(function(){return et})),n.d(e,"U",(function(){return nt})),n.d(e,"V",(function(){return rt}));var r=n(3),o=Object(r.getSetting)("currentUserIsAdmin",!1),c=Object(r.getSetting)("reviewRatingsEnabled",!0),i=Object(r.getSetting)("showAvatars",!0),u=Object(r.getSetting)("max_columns",6),s=Object(r.getSetting)("min_columns",1),a=Object(r.getSetting)("default_columns",3),l=Object(r.getSetting)("max_rows",6),b=Object(r.getSetting)("min_rows",1),d=Object(r.getSetting)("default_rows",3),g=Object(r.getSetting)("min_height",500),p=Object(r.getSetting)("default_height",500),f=Object(r.getSetting)("placeholderImgSrc",""),h=(Object(r.getSetting)("thumbnail_size",300),Object(r.getSetting)("isLargeCatalog")),O=Object(r.getSetting)("limitTags"),j=Object(r.getSetting)("hasProducts",!0),w=Object(r.getSetting)("hasTags",!0),m=Object(r.getSetting)("homeUrl",""),y=Object(r.getSetting)("couponsEnabled",!0),v=Object(r.getSetting)("shippingEnabled",!0),k=Object(r.getSetting)("taxesEnabled",!0),_=(Object(r.getSetting)("displayItemizedTaxes",!1),Object(r.getSetting)("hasDarkEditorStyleSupport",!1)),S=(Object(r.getSetting)("displayShopPricesIncludingTax",!1),Object(r.getSetting)("displayCartPricesIncludingTax",!1)),E=Object(r.getSetting)("productCount",0),P=Object(r.getSetting)("attributes",[]),C=Object(r.getSetting)("isShippingCalculatorEnabled",!0),x=(Object(r.getSetting)("isShippingCostHidden",!1),Object(r.getSetting)("woocommerceBlocksPhase",1)),A=Object(r.getSetting)("wcBlocksAssetUrl",""),T=Object(r.getSetting)("wcBlocksBuildUrl",""),D=Object(r.getSetting)("shippingCountries",{}),B=Object(r.getSetting)("allowedCountries",{}),R=Object(r.getSetting)("shippingStates",{}),N=Object(r.getSetting)("allowedStates",{}),M=Object(r.getSetting)("shippingMethodsExist",!1),I=Object(r.getSetting)("paymentGatewaySortOrder",[]),V=Object(r.getSetting)("checkoutShowLoginReminder",!0),z={id:0,title:"",permalink:""},H=Object(r.getSetting)("storePages",{shop:z,cart:z,checkout:z,privacy:z,terms:z}),L=H.shop.permalink,G=H.checkout.id,Q=H.checkout.permalink,q=H.privacy.permalink,F=H.privacy.title,U=H.terms.permalink,W=H.terms.title,$=H.cart.id,J=H.cart.permalink,K=Object(r.getSetting)("checkoutAllowsGuest",!1),X=Object(r.getSetting)("checkoutAllowsSignup",!1),Y=Object(r.getSetting)("loginUrl","/wp-login.php"),Z=n(23),tt=function(t,e){if(x>2)return Object(Z.registerBlockType)(t,e)},et=function(t,e){if(x>1)return Object(Z.registerBlockType)(t,e)},nt=function(){return x>2},rt=function(){return x>1}},64:function(t,e){!function(){t.exports=this.wp.serverSideRender}()},67:function(t,e){!function(){t.exports=this.wp.dom}()},7:function(t,e){!function(){t.exports=this.lodash}()},70:function(t,e){!function(){t.exports=this.wp.deprecated}()},73:function(t,e){!function(){t.exports=this.ReactDOM}()},74:function(t,e,n){"use strict";var r=n(5),o=n.n(r),c=n(0),i=n(1),u=(n(2),n(4));function s(t,e){var n=Object.keys(t);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(t);e&&(r=r.filter((function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable}))),n.push.apply(n,r)}return n}function a(t){for(var e=1;e<arguments.length;e++){var n=null!=arguments[e]?arguments[e]:{};e%2?s(Object(n),!0).forEach((function(e){o()(t,e,n[e])})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(n)):s(Object(n)).forEach((function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(n,e))}))}return t}e.a=function(t){var e=t.onChange,n=t.settings,r=n.button,o=n.price,s=n.rating,l=n.title;return Object(c.createElement)(c.Fragment,null,Object(c.createElement)(u.ToggleControl,{label:Object(i.__)("Product title","woo-gutenberg-products-block"),help:l?Object(i.__)("Product title is visible.","woo-gutenberg-products-block"):Object(i.__)("Product title is hidden.","woo-gutenberg-products-block"),checked:l,onChange:function(){return e(a(a({},n),{},{title:!l}))}}),Object(c.createElement)(u.ToggleControl,{label:Object(i.__)("Product price","woo-gutenberg-products-block"),help:o?Object(i.__)("Product price is visible.","woo-gutenberg-products-block"):Object(i.__)("Product price is hidden.","woo-gutenberg-products-block"),checked:o,onChange:function(){return e(a(a({},n),{},{price:!o}))}}),Object(c.createElement)(u.ToggleControl,{label:Object(i.__)("Product rating","woo-gutenberg-products-block"),help:s?Object(i.__)("Product rating is visible.","woo-gutenberg-products-block"):Object(i.__)("Product rating is hidden.","woo-gutenberg-products-block"),checked:s,onChange:function(){return e(a(a({},n),{},{rating:!s}))}}),Object(c.createElement)(u.ToggleControl,{label:Object(i.__)("Add to Cart button","woo-gutenberg-products-block"),help:r?Object(i.__)("Add to Cart button is visible.","woo-gutenberg-products-block"):Object(i.__)("Add to Cart button is hidden.","woo-gutenberg-products-block"),checked:r,onChange:function(){return e(a(a({},n),{},{button:!r}))}}))}},75:function(t,e,n){"use strict";var r=n(0),o=n(1),c=n(7),i=(n(2),n(4)),u=n(6);e.a=function(t){var e=t.columns,n=t.rows,s=t.setAttributes,a=t.alignButtons;return Object(r.createElement)(r.Fragment,null,Object(r.createElement)(i.RangeControl,{label:Object(o.__)("Columns","woo-gutenberg-products-block"),value:e,onChange:function(t){var e=Object(c.clamp)(t,u.A,u.y);s({columns:Number.isNaN(e)?"":e})},min:u.A,max:u.y}),Object(r.createElement)(i.RangeControl,{label:Object(o.__)("Rows","woo-gutenberg-products-block"),value:n,onChange:function(t){var e=Object(c.clamp)(t,u.C,u.z);s({rows:Number.isNaN(e)?"":e})},min:u.C,max:u.z}),Object(r.createElement)(i.ToggleControl,{label:Object(o.__)("Align Last Block","woo-gutenberg-products-block"),help:a?Object(o.__)("The last inner block will be aligned vertically.","woo-gutenberg-products-block"):Object(o.__)("The last inner block will follow other content.","woo-gutenberg-products-block"),checked:a,onChange:function(){return s({alignButtons:!a})}}))}},76:function(t,e){!function(){t.exports=this.wp.viewport}()},77:function(t,e){!function(){t.exports=this.wp.date}()},828:function(t,e,n){t.exports=n(902)},829:function(t,e){},830:function(t,e){},9:function(t,e){!function(){t.exports=this.React}()},902:function(t,e,n){"use strict";n.r(e);var r=n(0),o=n(1),c=n(23),i=n(6),u=n(54),s=n(31),a=Object(r.createElement)(s.SVG,{xmlns:"http://www.w3.org/2000/SVG",viewBox:"0 0 24 24"},Object(r.createElement)("path",{fill:"none",d:"M0 0h24v24H0V0z"}),Object(r.createElement)("path",{d:"M22 3H7c-.69 0-1.23.35-1.59.88L0 12l5.41 8.11c.36.53.97.89 1.66.89H22c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H7.07L2.4 12l4.66-7H22v14z"}),Object(r.createElement)("circle",{cx:"9",cy:"12",r:"1.5"}),Object(r.createElement)("circle",{cx:"14",cy:"12",r:"1.5"}),Object(r.createElement)("circle",{cx:"19",cy:"12",r:"1.5"})),l=(n(829),n(5)),b=n.n(l),d=n(14),g=n.n(d),p=n(15),f=n.n(p),h=n(12),O=n.n(h),j=n(16),w=n.n(j),m=n(17),y=n.n(m),v=n(10),k=n.n(v),_=n(22),S=n(64),E=n.n(S),P=n(4),C=(n(2),n(74)),x=n(75),A=n(11),T=n.n(A),D=n(7),B=n(44),R=n(36);n(830);function N(t){var e=function(){if("undefined"==typeof Reflect||!Reflect.construct)return!1;if(Reflect.construct.sham)return!1;if("function"==typeof Proxy)return!0;try{return Date.prototype.toString.call(Reflect.construct(Date,[],(function(){}))),!0}catch(t){return!1}}();return function(){var n,r=k()(t);if(e){var o=k()(this).constructor;n=Reflect.construct(r,arguments,o)}else n=r.apply(this,arguments);return y()(this,n)}}var M=function(t){w()(n,t);var e=N(n);function n(){var t;return g()(this,n),(t=e.apply(this,arguments)).state={list:[],loading:!0},t.renderItem=t.renderItem.bind(O()(t)),t.debouncedOnSearch=Object(D.debounce)(t.onSearch.bind(O()(t)),400),t}return f()(n,[{key:"componentDidMount",value:function(){var t=this,e=this.props.selected;Object(R.f)({selected:e}).then((function(e){t.setState({list:e,loading:!1})})).catch((function(){t.setState({list:[],loading:!1})}))}},{key:"onSearch",value:function(t){var e=this,n=this.props.selected;this.setState({loading:!0}),Object(R.f)({selected:n,search:t}).then((function(t){e.setState({list:t,loading:!1})})).catch((function(){e.setState({list:[],loading:!1})}))}},{key:"renderItem",value:function(t){var e=t.item,n=t.search,c=t.depth,i=void 0===c?0:c,u=["woocommerce-product-tags__item"];n.length&&u.push("is-searching"),0===i&&0!==e.parent&&u.push("is-skip-level");var s=e.breadcrumbs.length?"".concat(e.breadcrumbs.join(", "),", ").concat(e.name):e.name;return Object(r.createElement)(B.b,T()({className:u.join(" ")},t,{showCount:!0,"aria-label":Object(o.sprintf)(Object(o._n)("%1$d product tagged as %2$s","%1$d products tagged as %2$s",e.count,"woo-gutenberg-products-block"),e.count,s)}))}},{key:"render",value:function(){var t=this.state,e=t.list,n=t.loading,c=this.props,u=c.onChange,s=c.onOperatorChange,a=c.operator,l=c.selected,b={clear:Object(o.__)("Clear all product tags","woo-gutenberg-products-block"),list:Object(o.__)("Product Tags","woo-gutenberg-products-block"),noItems:Object(o.__)("Your store doesn't have any product tags.","woo-gutenberg-products-block"),search:Object(o.__)("Search for product tags","woo-gutenberg-products-block"),selected:function(t){return Object(o.sprintf)(Object(o._n)("%d tag selected","%d tags selected",t,"woo-gutenberg-products-block"),t)},updated:Object(o.__)("Tag search results updated.","woo-gutenberg-products-block")};return Object(r.createElement)(r.Fragment,null,Object(r.createElement)(B.a,{className:"woocommerce-product-tags",list:e,isLoading:n,selected:l.map((function(t){return Object(D.find)(e,{id:t})})).filter(Boolean),onChange:u,onSearch:i.w?this.debouncedOnSearch:null,renderItem:this.renderItem,messages:b,isHierarchical:!0}),!!s&&Object(r.createElement)("div",{className:l.length<2?"screen-reader-text":""},Object(r.createElement)(P.SelectControl,{className:"woocommerce-product-tags__operator",label:Object(o.__)("Display products matching","woo-gutenberg-products-block"),help:Object(o.__)("Pick at least two tags to use this setting.","woo-gutenberg-products-block"),value:a,onChange:s,options:[{label:Object(o.__)("Any selected tags","woo-gutenberg-products-block"),value:"any"},{label:Object(o.__)("All selected tags","woo-gutenberg-products-block"),value:"all"}]})))}}]),n}(r.Component);M.defaultProps={operator:"any"};var I=M,V=n(124),z=n(165);function H(t,e){var n=Object.keys(t);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(t);e&&(r=r.filter((function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable}))),n.push.apply(n,r)}return n}function L(t){for(var e=1;e<arguments.length;e++){var n=null!=arguments[e]?arguments[e]:{};e%2?H(Object(n),!0).forEach((function(e){b()(t,e,n[e])})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(n)):H(Object(n)).forEach((function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(n,e))}))}return t}function G(t){var e=function(){if("undefined"==typeof Reflect||!Reflect.construct)return!1;if(Reflect.construct.sham)return!1;if("function"==typeof Proxy)return!0;try{return Date.prototype.toString.call(Reflect.construct(Date,[],(function(){}))),!0}catch(t){return!1}}();return function(){var n,r=k()(t);if(e){var o=k()(this).constructor;n=Reflect.construct(r,arguments,o)}else n=r.apply(this,arguments);return y()(this,n)}}var Q=function(t){w()(n,t);var e=G(n);function n(){var t;return g()(this,n),(t=e.apply(this,arguments)).state={changedAttributes:{},isEditing:!1},t.startEditing=t.startEditing.bind(O()(t)),t.stopEditing=t.stopEditing.bind(O()(t)),t.setChangedAttributes=t.setChangedAttributes.bind(O()(t)),t.save=t.save.bind(O()(t)),t}return f()(n,[{key:"componentDidMount",value:function(){this.props.attributes.tags.length||this.setState({isEditing:!0})}},{key:"startEditing",value:function(){this.setState({isEditing:!0,changedAttributes:{}})}},{key:"stopEditing",value:function(){this.setState({isEditing:!1,changedAttributes:{}})}},{key:"setChangedAttributes",value:function(t){this.setState((function(e){return{changedAttributes:L(L({},e.changedAttributes),t)}}))}},{key:"save",value:function(){var t=this.state.changedAttributes;(0,this.props.setAttributes)(t),this.stopEditing()}},{key:"getInspectorControls",value:function(){var t=this.props,e=t.attributes,n=t.setAttributes,c=this.state.isEditing,i=e.columns,u=e.tagOperator,s=e.contentVisibility,a=e.orderby,l=e.rows,b=e.alignButtons;return Object(r.createElement)(_.InspectorControls,{key:"inspector"},Object(r.createElement)(P.PanelBody,{title:Object(o.__)("Product Tag","woo-gutenberg-products-block"),initialOpen:!e.tags.length&&!c},Object(r.createElement)(I,{selected:e.tags,onChange:function(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:[],e=t.map((function(t){return t.id}));n({tags:e})},operator:u,onOperatorChange:function(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:"any";return n({tagOperator:t})}})),Object(r.createElement)(P.PanelBody,{title:Object(o.__)("Layout","woo-gutenberg-products-block"),initialOpen:!0},Object(r.createElement)(x.a,{columns:i,rows:l,alignButtons:b,setAttributes:n})),Object(r.createElement)(P.PanelBody,{title:Object(o.__)("Content","woo-gutenberg-products-block"),initialOpen:!0},Object(r.createElement)(C.a,{settings:s,onChange:function(t){return n({contentVisibility:t})}})),Object(r.createElement)(P.PanelBody,{title:Object(o.__)("Order By","woo-gutenberg-products-block"),initialOpen:!1},Object(r.createElement)(V.a,{setAttributes:n,value:a})))}},{key:"renderEditMode",value:function(){var t=this,e=this.props,n=e.attributes,c=e.debouncedSpeak,i=this.state.changedAttributes,s=L(L({},n),i);return Object(r.createElement)(P.Placeholder,{icon:Object(r.createElement)(u.a,{srcElement:a,className:"block-editor-block-icon"}),label:Object(o.__)("Products by Tag","woo-gutenberg-products-block"),className:"wc-block-products-grid wc-block-product-tag"},Object(o.__)("Display a grid of products from your selected tags.","woo-gutenberg-products-block"),Object(r.createElement)("div",{className:"wc-block-product-tag__selection"},Object(r.createElement)(I,{selected:s.tags,onChange:function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:[],n=e.map((function(t){return t.id}));t.setChangedAttributes({tags:n})},operator:s.tagOperator,onOperatorChange:function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:"any";return t.setChangedAttributes({tagOperator:e})}}),Object(r.createElement)(P.Button,{isPrimary:!0,onClick:function(){t.save(),c(Object(o.__)("Showing Products by Tag block preview.","woo-gutenberg-products-block"))}},Object(o.__)("Done","woo-gutenberg-products-block")),Object(r.createElement)(P.Button,{className:"wc-block-product-tag__cancel-button",isTertiary:!0,onClick:function(){t.stopEditing(),c(Object(o.__)("Showing Products by Tag block preview.","woo-gutenberg-products-block"))}},Object(o.__)("Cancel","woo-gutenberg-products-block"))))}},{key:"renderViewMode",value:function(){var t=this.props,e=t.attributes,n=t.name,c=e.tags.length;return Object(r.createElement)(P.Disabled,null,c?Object(r.createElement)(E.a,{block:n,attributes:e}):Object(r.createElement)(P.Placeholder,{icon:Object(r.createElement)(u.a,{icon:a,className:"block-editor-block-icon"}),label:Object(o.__)("Products by Tag","woo-gutenberg-products-block"),className:"wc-block-products-grid wc-block-product-tag"},Object(o.__)("This block displays products from selected tags. Select at least one tag to display its products.","woo-gutenberg-products-block")))}},{key:"render",value:function(){var t=this,e=this.state.isEditing;return this.props.attributes.isPreview?z.a:i.s?Object(r.createElement)(r.Fragment,null,Object(r.createElement)(_.BlockControls,null,Object(r.createElement)(P.ToolbarGroup,{controls:[{icon:"edit",title:Object(o.__)("Edit"),onClick:function(){return e?t.stopEditing():t.startEditing()},isActive:e}]})),this.getInspectorControls(),e?this.renderEditMode():this.renderViewMode()):Object(r.createElement)(P.Placeholder,{icon:Object(r.createElement)(u.a,{icon:a,className:"block-editor-block-icon"}),label:Object(o.__)("Products by Tag","woo-gutenberg-products-block"),className:"wc-block-products-grid wc-block-product-tag"},Object(o.__)("This block displays products from selected tags. In order to preview this you'll first need to create a product and assign it some tags.","woo-gutenberg-products-block"))}}]),n}(r.Component),q=Object(P.withSpokenMessages)(Q);Object(c.registerBlockType)("woocommerce/product-tag",{title:Object(o.__)("Products by Tag","woo-gutenberg-products-block"),icon:{src:Object(r.createElement)(u.a,{srcElement:a}),foreground:"#96588a"},category:"woocommerce",keywords:[Object(o.__)("WooCommerce","woo-gutenberg-products-block")],description:Object(o.__)("Display a grid of products with selected tags.","woo-gutenberg-products-block"),supports:{align:["wide","full"],html:!1},example:{attributes:{isPreview:!0}},attributes:{columns:{type:"number",default:i.m},rows:{type:"number",default:i.o},alignButtons:{type:"boolean",default:!1},contentVisibility:{type:"object",default:{title:!0,price:!0,rating:!0,button:!0}},tags:{type:"array",default:[]},tagOperator:{type:"string",default:"any"},orderby:{type:"string",default:"date"},isPreview:{type:"boolean",default:!1}},edit:function(t){return Object(r.createElement)(q,t)},save:function(){return null}})}});