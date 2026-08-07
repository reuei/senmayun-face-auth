import { post, get } from './request'

// 初始化认证会话
export interface InitAuthParams {
  api_key: string
  api_secret: string
  user_id: string
  name?: string
  id_card?: string
  return_url?: string
  verify_type?: 'liveness' | 'face_compare' | 'full'
}

export interface InitAuthResult {
  token: string
  expire_time: string
  verify_url: string
}

export function initAuth(params: InitAuthParams) {
  return post<InitAuthResult>('/api/v1/auth/init', params)
}

// 验证token
export function verifyToken(token: string) {
  return post('/api/v1/auth/verify-token', { token })
}

// 获取认证结果
export function getAuthResult(token: string) {
  return post('/api/v1/auth/result', { token })
}

// 提交认证数据
export function submitVerification(token: string, data: any) {
  return post('/api/v1/auth/submit', { token, ...data })
}

// 人脸检测
export function faceDetect(image: string) {
  return post('/api/v1/face/detect', { image })
}

// 人脸比对
export function faceCompare(image1: string, image2: string) {
  return post('/api/v1/face/compare', { image1, image2 })
}
