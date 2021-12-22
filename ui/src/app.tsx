import type { Settings as LayoutSettings } from '@ant-design/pro-layout';
import { PageLoading } from '@ant-design/pro-layout';
import type { RunTimeLayoutConfig, RequestConfig } from 'umi';
import { history, Link } from 'umi';
import RightContent from '@/components/RightContent';
import Footer from '@/components/Footer';
import { currentUser as queryCurrentUser } from './services/ant-design-pro/api';
import { BookOutlined, LinkOutlined } from '@ant-design/icons';
import { RequestOptionsInit } from 'umi-request';
import { initUser } from './pages/user/Login';

const isDev = process.env.NODE_ENV === 'development';
const loginPath = '/user/login';

/** 获取用户信息比较慢的时候会展示一个 loading */
export const initialStateConfig = {
  loading: <PageLoading />,
};

/**
 * @see  https://umijs.org/zh-CN/plugins/plugin-initial-state
 * */
export async function getInitialState(): Promise<{
  settings?: Partial<LayoutSettings>;
  currentUser?: API.CurrentUser;
  menulist?: API.MenuItem[]
  fetchUserInfo?: () => Promise<API.GetUser | undefined>;
}> {
  const fetchUserInfo = async () => {
    try {
      const msg = await queryCurrentUser();
      if (msg.code === 10001) {
        history.push(loginPath);
        return undefined;
      }
      return msg.data;
    } catch (error) {
      history.push(loginPath);
    }
    return undefined;
  };
  // 如果是登录页面，不执行
  if (history.location.pathname !== loginPath) {
    const getUser = await fetchUserInfo();
    return {
      fetchUserInfo,
      currentUser: initUser(getUser),
      menulist: getUser?.menulist,
      settings: {},
    };
  }
  return {
    fetchUserInfo,
    settings: {},
  };
}

// ProLayout 支持的api https://procomponents.ant.design/components/layout
export const layout: RunTimeLayoutConfig = ({ initialState }: any) => {
  const levfunc = (list: API.MenuItem[], parentid: number, routs: any): any => {
    let arrs = list.filter(a => a.parentid === parentid && a.type === '菜单').map(a => ({
      hideInMenu: false,
      locale: false,
      name: a.name,
      key: a.id + '',
      routes: levfunc(list, a.id, routs),
      path: routs[a.name!!]?.path ?? ('/not' + a.id),
      icon: routs[a.name!!]?.icon ?? null
    }));

    return arrs ?? [];
  }

  const namefunc = (list: any): any => {
    let obj = {};
    if (!list) {
      return obj;
    }

    list.forEach((a: any) => {
      if (a.name && !obj[a.name!!]) {
        obj[a.name] = { path: a.path, icon: a.icon };
      }

      let cobj = namefunc(a.routes);
      obj = { ...obj, ...cobj };
    });

    return obj;
  }

  return {
    rightContentRender: () => <RightContent />,
    disableContentMargin: false,
    waterMarkProps: {
      content: initialState?.currentUser?.name,
    },
    menu: { autoClose: false },
    menuDataRender: (menuData: any) => {
      const menulist = initialState?.menulist || [];
      return levfunc(menulist, 0, namefunc(menuData));
    },
    footerRender: () => <Footer />,
    onPageChange: () => {
      const { location } = history;
      // 如果没有登录，重定向到 login
      if (!initialState?.currentUser && location.pathname !== loginPath) {
        history.push(loginPath);
      }
    },
    links: isDev
      ? [
        // <Link to="/umi/plugin/openapi" target="_blank">
        //   <LinkOutlined />
        //   <span>OpenAPI 文档</span>
        // </Link>,
        // <Link to="/~docs">
        //   <BookOutlined />
        //   <span>业务组件文档</span>
        // </Link>,
      ]
      : [],
    menuHeaderRender: undefined,
    // 自定义 403 页面
    // unAccessible: <div>unAccessible</div>,
    // 增加一个 loading 的状态
    // childrenRender: (children) => {
    //   if (initialState.loading) return <PageLoading />;
    //   return children;
    // },
    ...initialState?.settings,
  };
};


const _BaseUrl = "http://cts.icheguo.com:8090";

const authHeaderInterceptor = (url: string, options: RequestOptionsInit) => {
  const authHeader = {};
  url = _BaseUrl + url;
  return {
    url: `${url}`,
    options: { ...options, interceptors: true, headers: authHeader },
  };
};

export const request: RequestConfig = {
  // 新增自动添加AccessToken的请求前拦截器
  requestInterceptors: [authHeaderInterceptor],
  credentials: 'include',
};